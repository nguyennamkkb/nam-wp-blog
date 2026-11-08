<?php
/**
 * Idempotent fixture seeder for the Latent Playwright suite.
 *
 * Run with: ddev wp eval-file tests/fixtures/seed.php
 *
 * The seeder only creates content that is missing, keyed by slug. Re-running it
 * against the demo database is a no-op; running it against an empty database
 * creates the posts, pages, series and featured images the E2E suite expects.
 *
 * @package latent
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "This script must run inside WordPress via wp eval-file.\n" );
	exit( 1 );
}

/**
 * Logs one line through WP-CLI when available.
 *
 * @param string $message Message to print.
 * @return void
 */
function latent_fixture_log( string $message ): void {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $message );
		return;
	}

	echo $message . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Returns the ID of a post/page with a given slug, or 0.
 *
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return int
 */
function latent_fixture_find( string $slug, string $post_type ): int {
	$post = get_page_by_path( $slug, OBJECT, $post_type );

	return $post instanceof WP_Post ? (int) $post->ID : 0;
}

/**
 * Ensures a category exists and returns its slug.
 *
 * @param string $slug Category slug.
 * @param string $name Category name.
 * @return string
 */
function latent_fixture_category( string $slug, string $name ): string {
	if ( ! term_exists( $slug, 'category' ) ) {
		wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
	}

	return $slug;
}

/**
 * Ensures the three series terms exist.
 *
 * @return array<int, string> Series slugs.
 */
function latent_fixture_series(): array {
	$terms = array(
		'rag-tu-so-0'          => array(
			'name'        => 'RAG từ số 0',
			'description' => 'Chuỗi bài đi từ chunking, vector store đến latency của một hệ RAG chạy thật.',
		),
		'production-ai-series' => array(
			'name'        => 'Production AI',
			'description' => 'Chi phí, evaluation và vận hành LLM trong production.',
		),
		'ai-agents-thuc-chien' => array(
			'name'        => 'AI Agents thực chiến',
			'description' => 'Tool use, failure modes và cách kiểm soát agent.',
		),
	);

	foreach ( $terms as $slug => $term ) {
		if ( ! term_exists( $slug, 'series' ) ) {
			wp_insert_term(
				$term['name'],
				'series',
				array(
					'slug'        => $slug,
					'description' => $term['description'],
				)
			);
		}
	}

	return array_keys( $terms );
}

/**
 * Returns generated PNG bytes, falling back to a 1×1 transparent pixel when GD
 * is unavailable.
 *
 * @param string $slug Used to derive a stable tint.
 * @return string
 */
function latent_fixture_png( string $slug ): string {
	if ( function_exists( 'imagecreatetruecolor' ) ) {
		$image = imagecreatetruecolor( 1200, 630 );

		if ( false !== $image ) {
			$hash  = crc32( $slug );
			$red   = 32 + ( $hash & 0x3F );
			$green = 40 + ( ( $hash >> 6 ) & 0x3F );
			$blue  = 56 + ( ( $hash >> 12 ) & 0x3F );
			$bg    = imagecolorallocate( $image, $red, $green, $blue );
			$line  = imagecolorallocate( $image, min( 255, $red + 40 ), min( 255, $green + 40 ), min( 255, $blue + 40 ) );

			imagefilledrectangle( $image, 0, 0, 1200, 630, $bg );
			imagefilledrectangle( $image, 96, 430, 1104, 436, $line );
			imagefilledrectangle( $image, 96, 460, 780, 466, $line );

			ob_start();
			imagepng( $image );
			$bytes = (string) ob_get_clean();
			imagedestroy( $image );

			if ( '' !== $bytes ) {
				return $bytes;
			}
		}
	}

	return (string) base64_decode(
		'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
		true
	);
}

/**
 * Returns an attachment ID for a fixture slug, creating one when needed.
 *
 * @param string $slug Fixture slug.
 * @return int
 */
function latent_fixture_attachment( string $slug ): int {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_latent_fixture_slug', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$upload = wp_upload_bits( 'latent-fixture-' . sanitize_file_name( $slug ) . '.png', null, latent_fixture_png( $slug ) );

	if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
		latent_fixture_log( sprintf( 'Could not upload featured image for %s: %s', $slug, (string) ( $upload['error'] ?? 'unknown' ) ) );

		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => 'Latent fixture — ' . $slug,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) || 0 === $attachment_id ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
	);

	update_post_meta( $attachment_id, '_latent_fixture_slug', $slug );

	return (int) $attachment_id;
}

/**
 * Ensures a featured image and returns the attachment ID.
 *
 * @param int    $post_id Post ID.
 * @param string $slug    Fixture slug.
 * @return int
 */
function latent_fixture_thumbnail( int $post_id, string $slug ): int {
	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id > 0 ) {
		return $thumbnail_id;
	}

	$attachment_id = latent_fixture_attachment( $slug );

	if ( $attachment_id > 0 ) {
		set_post_thumbnail( $post_id, $attachment_id );
	}

	return $attachment_id;
}

$latent_fixture_posts = array(
	array(
		'slug'     => 'rag-tu-so-0-chunking',
		'title'    => 'RAG từ số 0: chunking nào thực sự tốt?',
		'date'     => '2026-11-07 09:00:00',
		'series'   => 'rag-tu-so-0',
		'category' => 'llm-systems',
		'excerpt'  => 'So sánh fixed-size, recursive và semantic chunking bằng cùng một bộ tài liệu, cùng một model, cùng một thước đo.',
		'content'  => '<!-- wp:paragraph --><p>Chunking là quyết định rẻ nhất nhưng ảnh hưởng lớn nhất đến chất lượng RAG.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Vấn đề</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Chunk quá nhỏ làm mất ngữ cảnh; chunk quá lớn làm loãng embedding.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ba chiến lược</h2><!-- /wp:heading --><!-- wp:code --><pre class="wp-block-code language-python"><code>chunks = split_recursive(text, max_tokens=512, overlap=64)</code></pre><!-- /wp:code --><!-- wp:heading --><h2>Kết quả</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Recursive chunking thắng ở recall@5 trên bộ câu hỏi nội bộ.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Kết luận</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Với corpus có cấu trúc heading rõ ràng, recursive là mặc định tốt.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'do-cost-llm-production',
		'title'    => 'Đo cost LLM theo request trong production',
		'date'     => '2026-11-05 09:00:00',
		'series'   => 'production-ai-series',
		'category' => 'production-ai',
		'excerpt'  => 'Một cách đo chi phí LLM theo từng request thay vì nhìn hoá đơn cuối tháng.',
		'content'  => '<!-- wp:paragraph --><p>Hoá đơn cuối tháng không nói cho bạn biết request nào đắt.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Đo gì</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Token vào, token ra, retry và cache hit.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Cách làm</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Gắn một request id vào mỗi lần gọi model và tổng hợp theo ngày.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'ai-agents-tool-use-failure-modes',
		'title'    => 'AI agents: tool use và failure modes',
		'date'     => '2026-11-03 09:00:00',
		'series'   => 'ai-agents-thuc-chien',
		'category' => 'ai-tooling',
		'excerpt'  => 'Những kiểu hỏng thường gặp khi agent gọi tool và cách phát hiện sớm.',
		'content'  => '<!-- wp:paragraph --><p>Agent chỉ đáng tin khi tool call của nó có thể quan sát được.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Failure modes</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Loop vô hạn, chọn sai tool và tin vào output rỗng.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Giảm rủi ro</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Giới hạn số bước, validate schema và log mọi lần gọi.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'chay-llm-local-32gb-ram',
		'title'    => 'Chạy LLM local trên máy 32GB RAM',
		'date'     => '2026-11-01 09:00:00',
		'series'   => '',
		'category' => 'experiments',
		'excerpt'  => 'Thử nghiệm quantisation và context length trên một máy desktop 32GB.',
		'content'  => '<!-- wp:paragraph --><p>Mục tiêu là một môi trường thử nghiệm đủ nhanh để đánh giá prompt.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Phần cứng</h2><!-- /wp:heading --><!-- wp:paragraph --><p>32GB RAM, GPU 12GB và một model 7B quantised.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Kết quả</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Đủ dùng cho thử nghiệm, không đủ cho production.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'giam-latency-rag-40',
		'title'    => '3 cách giảm latency RAG 40%',
		'date'     => '2026-10-29 09:00:00',
		'series'   => 'rag-tu-so-0',
		'category' => 'llm-systems',
		'excerpt'  => 'Ba thay đổi nhỏ ở retrieval, prompt và streaming giúp giảm latency đo được.',
		'content'  => '<!-- wp:paragraph --><p>Latency RAG thường nằm ở retrieval chứ không phải ở model.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Đo trước</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Tách thời gian retrieve, rerank và generate.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ba thay đổi</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Cache embedding, giảm top-k và stream câu trả lời.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'ai-engineer-ngoai-prompt',
		'title'    => 'AI engineer cần biết gì ngoài prompt?',
		'date'     => '2026-10-27 09:00:00',
		'series'   => 'production-ai-series',
		'category' => 'craft',
		'excerpt'  => 'Prompt chỉ là một phần; phần còn lại là dữ liệu, hệ thống và cách đo.',
		'content'  => '<!-- wp:paragraph --><p>Prompt engineering là kỹ năng cần, nhưng không đủ.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ngoài prompt</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Data pipeline, evaluation và observability.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Kết luận</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Hệ thống tốt làm prompt trung bình trở nên hữu ích.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'vector-database-cho-rag',
		'title'    => 'Vector database: chọn gì cho RAG?',
		'date'     => '2026-10-25 09:00:00',
		'series'   => 'rag-tu-so-0',
		'category' => 'ai-tooling',
		'excerpt'  => 'So sánh pgvector, Qdrant và vector store managed theo workload thực tế.',
		'content'  => '<!-- wp:paragraph --><p>Chọn vector store theo workload, không theo benchmark marketing.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Tiêu chí</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Filter metadata, recall và chi phí vận hành.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Kết luận</h2><!-- /wp:heading --><!-- wp:paragraph --><p>pgvector đủ cho phần lớn dự án nhỏ.</p><!-- /wp:paragraph -->',
	),
	array(
		'slug'     => 'evaluation-llm-vibe-check',
		'title'    => 'Evaluation LLM: từ vibe check đến số đo',
		'date'     => '2026-10-23 09:00:00',
		'series'   => 'production-ai-series',
		'category' => 'production-ai',
		'excerpt'  => 'Biến cảm giác “có vẻ tốt hơn” thành một bộ evaluation lặp lại được.',
		'content'  => '<!-- wp:paragraph --><p>Vibe check là điểm bắt đầu, không phải bằng chứng.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Bộ câu hỏi</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Bắt đầu với 50 câu hỏi đại diện cho người dùng thật.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Đo lường</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Chấm điểm theo rubric và lưu kết quả theo phiên bản.</p><!-- /wp:paragraph -->',
	),
);

$latent_fixture_pages = array(
	'home'    => array(
		'title'   => 'Home',
		'content' => '<!-- wp:paragraph --><p>Trang chủ Latent.</p><!-- /wp:paragraph -->',
	),
	'blog'    => array(
		'title'   => 'Blog',
		'content' => '',
	),
	'series'  => array(
		'title'   => 'Series',
		'content' => '<!-- wp:latent/series-list /-->',
	),
	'about'   => array(
		'title'   => 'About',
		'content' => '<!-- wp:paragraph --><p>Về tác giả Latent.</p><!-- /wp:paragraph -->',
	),
	'now'     => array(
		'title'   => 'Now',
		'content' => '<!-- wp:paragraph --><p>Đang làm gì ở hiện tại.</p><!-- /wp:paragraph -->',
	),
	'uses'    => array(
		'title'   => 'Uses',
		'content' => '<!-- wp:paragraph --><p>Công cụ dùng hằng ngày.</p><!-- /wp:paragraph -->',
	),
	'contact' => array(
		'title'   => 'Contact',
		'content' => '<!-- wp:paragraph --><p>Liên hệ qua email.</p><!-- /wp:paragraph -->',
	),
	'privacy' => array(
		'title'   => 'Privacy',
		'content' => '<!-- wp:paragraph --><p>Chính sách riêng tư.</p><!-- /wp:paragraph -->',
	),
);

$latent_series_slugs = latent_fixture_series();
$latent_created      = 0;

foreach ( $latent_fixture_posts as $latent_fixture ) {
	$latent_post_id = latent_fixture_find( $latent_fixture['slug'], 'post' );

	if ( 0 === $latent_post_id ) {
		$latent_post_id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $latent_fixture['title'],
				'post_name'    => $latent_fixture['slug'],
				'post_excerpt' => $latent_fixture['excerpt'],
				'post_content' => $latent_fixture['content'],
				'post_date'    => $latent_fixture['date'],
				'post_author'  => 1,
			),
			true
		);

		if ( is_wp_error( $latent_post_id ) ) {
			latent_fixture_log( sprintf( 'Could not create post %s: %s', $latent_fixture['slug'], $latent_post_id->get_error_message() ) );
			continue;
		}

		$latent_post_id = (int) $latent_post_id;
		++$latent_created;

		$latent_fixture_category( $latent_fixture['category'], ucwords( str_replace( '-', ' ', $latent_fixture['category'] ) ) );
		wp_set_post_terms( $latent_post_id, array( $latent_fixture['category'] ), 'category', false );

		if ( '' !== $latent_fixture['series'] && in_array( $latent_fixture['series'], $latent_series_slugs, true ) ) {
			wp_set_object_terms( $latent_post_id, array( $latent_fixture['series'] ), 'series', false );
		}
	}

	latent_fixture_thumbnail( (int) $latent_post_id, $latent_fixture['slug'] );
}

foreach ( $latent_fixture_pages as $latent_slug => $latent_page ) {
	if ( 0 === latent_fixture_find( $latent_slug, 'page' ) ) {
		$latent_page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $latent_page['title'],
				'post_name'    => $latent_slug,
				'post_content' => $latent_page['content'],
				'post_author'  => 1,
			),
			true
		);

		if ( is_wp_error( $latent_page_id ) ) {
			latent_fixture_log( sprintf( 'Could not create page %s: %s', $latent_slug, $latent_page_id->get_error_message() ) );
			continue;
		}

		++$latent_created;
	}
}

// Point WordPress at the fixture front page and posts page when unset.
$latent_front_page = (int) get_option( 'page_on_front' );
$latent_posts_page = (int) get_option( 'page_for_posts' );

if ( 0 === $latent_front_page || ! get_post( $latent_front_page ) instanceof WP_Post ) {
	$latent_home_id = latent_fixture_find( 'home', 'page' );

	if ( $latent_home_id > 0 ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $latent_home_id );
	}
}

if ( 0 === $latent_posts_page || ! get_post( $latent_posts_page ) instanceof WP_Post ) {
	$latent_blog_id = latent_fixture_find( 'blog', 'page' );

	if ( $latent_blog_id > 0 ) {
		update_option( 'page_for_posts', $latent_blog_id );
	}
}

$latent_post_count   = (int) wp_count_posts( 'post' )->publish;
$latent_page_count   = (int) wp_count_posts( 'page' )->publish;
$latent_series_count = (int) wp_count_terms(
	array(
		'taxonomy'   => 'series',
		'hide_empty' => false,
	)
);

$latent_missing_thumbnails = 0;

foreach ( $latent_fixture_posts as $latent_fixture ) {
	$latent_post_id = latent_fixture_find( $latent_fixture['slug'], 'post' );

	if ( $latent_post_id > 0 && 0 === (int) get_post_thumbnail_id( $latent_post_id ) ) {
		++$latent_missing_thumbnails;
	}
}

latent_fixture_log(
	sprintf(
		'Fixtures ready: %d posts, %d pages, %d series, %d created this run, %d fixture posts missing featured images.',
		$latent_post_count,
		$latent_page_count,
		$latent_series_count,
		$latent_created,
		$latent_missing_thumbnails
	)
);

if ( $latent_post_count < 8 || $latent_page_count < 8 || $latent_series_count < 3 ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::error( 'Fixture minimums were not met (expected at least 8 posts, 8 pages and 3 series).' );
	}

	exit( 1 );
}
