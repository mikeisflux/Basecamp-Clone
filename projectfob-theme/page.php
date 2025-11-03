<?php
/**
 * Template for displaying pages
 *
 * @package ProjectFOB_Theme
 */

get_header();
?>

<div class="page-content-wrapper py-5">
    <div class="container">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
                <header class="entry-header mb-4">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="entry-thumbnail mb-4">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content card">
                    <?php
                    the_content();

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . __( 'Pages:', 'projectfob-theme' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div>
            </article>

            <?php
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
