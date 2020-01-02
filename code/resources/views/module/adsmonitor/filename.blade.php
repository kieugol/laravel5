<?php
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
?>
<div style="width:200px">
<?php if(in_array($ext, array("jpg", "jpeg", "gif", "png"))): ?>
<img src="<?php echo env('APP_URL') . "/upload/" . $filename ?>" style="max-width: 200px; max-height: 120px; margin: 0 auto; display: block;" />
<?php endif; ?>

<?php if(in_array($ext, array("mp4", "mp3"))): ?>
    <video width="200" height="120" controls>
        <source src="<?php echo env('APP_URL') . "/upload/" . $filename ?>" type="video/mp4">
        {{--<source src="movie.ogg" type="video/ogg">--}}
        Your browser does not support the video tag.
    </video>
<?php endif; ?>
</div>