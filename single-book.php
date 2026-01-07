<?php
/**
 * Single Book Template
 * 
 * OPTIONAL: Copy this file to your theme directory to customize book display
 * Location: your-theme/single-book.php
 * 
 * This template will override the default single post template for books
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        while (have_posts()) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>

                <div class="book-layout">
                    <?php if (has_post_thumbnail()): ?>
                    <div class="book-cover">
                        <?php the_post_thumbnail('large', ['class' => 'book-cover-image']); ?>
                    </div>
                    <?php endif; ?>

                    <div class="book-content-wrapper">
                        <?php
                        // Book meta is automatically added by FrontendDisplay class
                        // But you can also use the shortcode: echo do_shortcode('[book_info]');
                        ?>

                        <div class="entry-content">
                            <?php
                            the_content();

                            wp_link_pages([
                                'before' => '<div class="page-links">' . esc_html__('Pages:', 'book-manager'),
                                'after'  => '</div>',
                            ]);
                            ?>
                        </div>
                    </div>
                </div>

                <footer class="entry-footer">
                    <?php
                    // You can add tags, categories, or other taxonomies here
                    ?>
                </footer>

            </article>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;

        endwhile;
        ?>

    </main>
</div>

<?php
get_sidebar();
get_footer();
?>