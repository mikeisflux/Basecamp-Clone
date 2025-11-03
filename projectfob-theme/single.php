<?php
/**
 * Template for displaying single posts
 *
 * @package ProjectFOB_Theme
 */

get_header();
?>

<div class="single-post-wrapper py-5">
    <div class="container">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>
                <header class="entry-header mb-4 text-center">
                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <div class="entry-meta">
                        <span class="posted-on">
                            <?php echo get_the_date(); ?>
                        </span>
                        <span class="byline">
                            by <?php the_author_posts_link(); ?>
                        </span>
                        <?php if ( has_category() ) : ?>
                            <span class="cat-links">
                                in <?php the_category( ', ' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="entry-thumbnail mb-4">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content card">
                    <?php
                    the_content( sprintf(
                        wp_kses(
                            /* translators: %s: Name of current post. Only visible to screen readers */
                            __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'projectfob-theme' ),
                            array(
                                'span' => array(
                                    'class' => array(),
                                ),
                            )
                        ),
                        wp_kses_post( get_the_title() )
                    ) );

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'projectfob-theme' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div>

                <?php if ( has_tag() ) : ?>
                    <footer class="entry-footer mt-4">
                        <div class="tags-links">
                            <?php the_tags( '<strong>' . __( 'Tags:', 'projectfob-theme' ) . '</strong> ', ', ' ); ?>
                        </div>
                    </footer>
                <?php endif; ?>
            </article>

            <?php
            the_post_navigation( array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'projectfob-theme' ) . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'projectfob-theme' ) . '</span> <span class="nav-title">%title</span>',
            ) );

            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;

        endwhile; // End of the loop.
        ?>
    </div>
</div>

<?php
get_footer();
