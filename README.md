# image-effects
'image-effects' Wordpress Plugin allows you to do image processing with many awesome image effects by using shortcodes!


# Environment:
1. PHP 5.2 above with ImageMagick Extension installed
2. Wordpress 3.4 above

#	Installation
This section describes how to install the plugin and get it working.

1. In your WordPress Administration Panels, click on WP Dashboard -> Plugins -> Add New plugin
2. Click 'upload plugin' button
3. Browse and select 'image-effects.zip' and click 'install'
4. To turn the 'Image Effects Plugin' on, click Activate.

Alternatively you can also follow the following steps to install the Image Effects plugin

1. Upload the image-effects folder to the to the /wp-content/plugins/ directory
2. Activate the plugin through the ‘Plugins’ menu in WordPress

# Simple User Guide / Example

1. Text on Image (style : text_on_image):

```
[image_effects style="text_on_image" text="Hello" font-size="20" font-offset-x="20" font-offset-y="30" pic1="http://kevix.rf.gd/material/i1.jpg"]
```

**Attribute:**

| Attribute Name  | Description  | Is Required | Default Value |
| ------------ | --------------- | :-----: | -------------- |
| pic1      | The URL of Image | Y | --- |
| text      | Text On the Image | Y | --- |
| font-size      | Font Size | N | 36 |
| font-offset-x | Left margin (px) start to write Text | N | 0 |
| font-offset-y | Top margin (px) start to write Text | N | 0 |

**Result:**
Original Image:

![](http://kevix.rf.gd/material/i1.jpg)

Processed Image:

![](http://kevix.rf.gd/material/r1.jpg)
