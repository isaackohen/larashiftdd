<?php

  $all_files = glob("/home/ploi/s.davidkohen/public/woo/files/*.*");
  for ($i=0; $i<count($all_files); $i++)
    {
      $image_name = $all_files[$i];
      $supported_format = array('gif','jpg','jpeg','png');
      $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
      if (in_array($ext, $supported_format))
          {
            echo '<img src="'.$image_name .'" alt="'.$image_name.'" />'."<br /><br />";
          } else {
              continue;
          }
    }

  ?>