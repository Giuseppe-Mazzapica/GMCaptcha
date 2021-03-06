GM Captcha
==========

A simple captcha (and honeypot) implementation for WordPress.

It requires **PHP 5.4+**.

![GM Captcha Screenshot](http://i.imgur.com/Q8xGidV.gif "GM Captcha (big sized) Screenshot")

-----------


##Table of Contents##

- [Add to your projects](#add-to-your-projects)
- [Usage](#usage)
  - [Print Fields](#print-fields)
  - [Check Value](#check-value)
  - [Configure](#configure)
    - [Change Defaults](#change-defaults)
    - [Function Arguments](#function-arguments)
  - [Honeypot](#honeypot)
- [Notes](#notes)
- [License and Credits](#license-and-credits)


---

##Add to your projects##

In `composer.json` add `"zoomlab/captcha": "dev-master"` to `require` object and `"https://github.com/Giuseppe-Mazzapica/GMCaptcha"` to `repositories` array.

Something like:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Giuseppe-Mazzapica/GMCaptcha"
        }
    ],
    "require": {
        "php": ">=5.4",
        "zoomlab/captcha": "dev-master"
    }
    

##Usage##


###Print Fields###

By default GM Captcha prints 4 fields:

 - the captcha image
 - a text field where insert the solution
 - an hidden field needed for verification
 - an honeypot field
 
the only thing needed to print this field is just call the function **`GM\captcha()`**. Nothing else.

Tha function will not create the form, will not add any style, and fields have a minimal markup to easy the styling.
 
    
###Check Value###

When the form is submitted, the only thing needed to check the captcha is call **`GM\check_captcha()`**.

The function will autodetected everything and return `TRUE` if the entered value is the right one.

###Configure###

GM Captcha is very flexible, and allow to configure pretty everything. There are 2 ways to configure the output:

 - change the defaults
 - pass arguments to `GM\captcha()`
 
Of course, both methods can be used togheter and customization passed to function will always overwrite defaults.

####Change Defaults####

Defaults can be changed using some filter hooks:

 - `'gmcaptcha_base_img'` to customize the base image for captcha. Hooked functions must return a full url
 - `'gmcaptcha_default_size'` to customize the captcha size. Hooked functions must return a 2-items array: 1st is width, 2nd is height
 - `'gmcaptcha_defaults'` to customize different aspects of the captcha. Hooked functions must return an array, where accepted key/default values are:

          $options = [
            'lines'     => 10, // number of  lines to create "noise"
            'dots'      => 100, // number of dots to create "noise"
            'color'     => '7d1ac5', // foreground color, used for captcha code and noise
            'font'      => "/BrokenGlass.ttf", // the font. Must be an absolute path to a .ttf file
            'chars_num' => 4 // number of characters in the captcha code
          ];

 - `'gmcaptcha_container'` advanced filter that allow to completely customize the Pimple container used by plugin
         
####Function Arguments####

The function `GM\captcha()` can take an array of arguments to customize the appearance of captcha.
This array is identical to the one that should be returned on the `'gmcaptcha_defaults'` filter, plus 2 other args:
`'width'` and `'height'`.

An example of fully customized function:

    GM\captcha([
      'lines'     => 50,
      'dots'      => 0,
      'color'     => 'FF0000',
      'font'      => "/my/theme/path/fonts/custom.ttf",
      'chars_num' => 6,
      'width'     => 300,
      'height'    => 150
    ]);
    
Please note that `'width'` and `'height'` arguments **works only if both are used**: if only one is set, it will be ignored.

###Honeypot###

As said, GM Captcha embed a simple honeypot check: is just a text field hidden via inline style (on parent `span`)
that must be empty, and if some robots will fill that field validation fail.

There is no additional validation to make for the honeypot works, all is handled via `GM\check_captcha()`.

However, is possible disable the honeypot returning an empty value on `'gmcaptcha_use_honeypot'` filter hook:

    add_filter('gmcaptcha_use_honeypot', '__return_false');
    
###Other Customizations###

There are few other hooks available:

 - `'gmcaptcha_container_class'` to customize the class for the `div` that wrap the captcha fields (empty by default)
 - `'gmcaptcha_label'` to customize the text shown next to the text field, default: `__('Type the captcha:', 'gmcaptcha')`
 
All the (few) strings used in the plugin are translatable, the text domain is 'gmcaptcha' and plugins already comes with italian translation.


##Notes##

To create the images the plugin uses an extension of core `WP_Image_Editor` classes, so auto-choose as image driver the first available from
`ImageMagik` an `GD` using [`wp_get_image_editor`](http://codex.wordpress.org/Function_Reference/wp_get_image_editor). Image quality is better when `ImageMagik` is used (default when available).

To re-generate the image upon click (when unreadable code is shown) plugin uses a little javascript script (971 byte in minimized version).
This script require jQuery (*properly* enqueued using `wp_enqueue_script`).
Minimized version is automatically used when `WP_DEBUG` is set to `FALSE`.


##License and Credits##

GM Captcha is released under GPL2+. It makes use of third party scripts and assets:

 - [Pimple](http://pimple.sensiolabs.org) by Fabien Potencier that is released under MIT
 
 - the font 'Broken Glass' designed by [JLH Fonts](http://jlhfonts.blogspot.it) and released under public domain.










    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
