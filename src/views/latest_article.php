<?php if ($latest_posts->have_posts()) : ?>
    <ul class="rp-latest-articles">
        <?php while ($latest_posts->have_posts()) : $latest_posts->the_post(); ?>
            <li>
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('thumbnail'); ?>
                    <?php else : ?>
                        <img src="https://via.placeholder.com/150" alt="No Image Available">
                    <?php endif; ?>
                </a>

                <a href="<?php the_permalink(); ?>">
                    <h3><?php the_title(); ?></h3>
                </a>

                <p class="post-date"><?php echo get_the_date(); ?></p>
            </li>
        <?php endwhile; ?>
    </ul>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p>Pas d'article récent.</p>
<?php endif; ?>