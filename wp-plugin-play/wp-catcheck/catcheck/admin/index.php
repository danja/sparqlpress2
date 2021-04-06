<?php
echo 'admin Index';

$args = array(
    'numberposts' => -1
  );
   
$all_posts = get_posts($args);

foreach ($all_posts as $post)  {
  echo "<br/><br/><br/>ID<br/>";
  echo $post->ID; 
  echo "<br/><br/><br/>ID<br/>";
  echo $post->guid; 
/*
    ?>
        <div>
            <?php the_date(); ?>
            <br />
            <?php the_title(); ?>   
            <?php the_excerpt(); ?>
        </div>
    <?php
    */
}


?>