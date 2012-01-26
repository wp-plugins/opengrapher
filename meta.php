<?php

$opengrapher_meta = opengrahper_get_post_meta();

?>

<div style="clear:both;" class="wrap">
  <?php foreach(opengrapher_properties() as $p): $content = opengrapher_value($p); ?>
    <h3 class="hndle">
      Open Graph <?php echo ucwords(str_replace("_"," ",$p)) ?>
      <?php if(!$content && $d = opengrapher_value($p,true)): ?>
        <small> (Will default to: <?php echo $d ?>) </small>
      <?php endif; ?>
    </h3>
    <?php if($p == 'image'): ?>
      <input class="upload_image_value the-value" type="text" size="36" name="_opengrapher_meta[opengrapher_<?php echo $p ?>]" value="<?php echo isset($opengrapher_meta["opengrapher_".$p]) ? $opengrapher_meta["opengrapher_".$p] : $content  ?>" /> 
      <input class="upload_image_button" type="button" value="Upload Image" /> Enter a URL or Click "Upload an Image"
    <?php else: ?>
      <input style="width:100%;" type="text" name="_opengrapher_meta[opengrapher_<?php echo $p ?>]" value="<?php echo isset($opengrapher_meta["opengrapher_".$p]) ? $opengrapher_meta["opengrapher_".$p] : $content ?>" />
    <?php endif; ?>
    <br />
    <br />
    <br />
    
  <?php endforeach; ?>      
</div>