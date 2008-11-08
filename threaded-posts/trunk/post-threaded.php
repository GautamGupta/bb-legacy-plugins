        <div class="threadauthor">
            <?php post_author_avatar_link(); ?>
            <p>
                <strong><?php post_author_link(); ?></strong><br />
                <small><?php post_author_title_link(); ?></small>
            </p>
        </div>

        <div class="threadpost">
            <div id="post_contents-<?php post_id(); ?>" class="post"><?php post_text(); ?></div>
            <div class="poststuff">
                <?php printf( __('Posted %s ago'), bb_get_post_time() ); ?>
                <a href="<?php post_anchor_link(); ?>">#</a>
                    <a href="/moo" onClick="reply_to('<?php post_id(); ?>'); return false;">Reply</a>
                <?php bb_post_admin(); ?>
            </div>
        </div>

<?php
    $children = threaded_posts_post_children();
    if (count($children)) :
?>
    <ol<?php alt_class('post', 'list:post')?> >
        <?php foreach ($children as $bb_post) : ?>
            <li id="post-<?php post_id(); ?>">
                <?php bb_post_template(); ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
