
# pshot

## Overview

pshot is small implementation of the mshot_class [automatic/mshots](https://github.com/Automattic/mshots) to capture web images and process on a local server. It uses phantomjs to capture images using its rasterise feature. The processing of images is queued to the server using interprocess messaging provided through php semaphores.[creating-a-message-queue-in-php](http://www.ebrueggeman.com/blog/creating-a-message-queue-in-php)


demo page is [here](http://www.dinradio.com.au/resources/online-music-services-au): 

## Installation

1. install phantomjs
2. configure queue - qworker
3. create pshot working directory on webserver    default to ../pshots/p1
4. add pshot shortcode to wordpress


## Details

### pshot class

define constants for location to store thumbnails and default gif

    const location_base = '/var/www/html/static/thumbnails';
    const snapshot_default = 'http://example.com.au/pshots/default.gif';

### queue workers

cron job to start worker
modify install location in worker.php to suit

    * * * * * sh /home/qworker/manager.sh

Message pulled from queue - id:803f4b95012b22b1da8e4b4fbdf501901f0ce0ad, file:/usr/local/apache2/htdocs/static/thumbnails/7fc/7fce36436af2faf8f4fd05f8068a6f4da14b6834/b69766eae375aa69a5370fd6441bb222.jpg, url:http://www.heraldsun.com.au
803f4b95012b22b1da8e4b4fbdf501901f0ce0ad.js/usr/local/bin/phantomjs 803f4b95012b22b1da8e4b4fbdf501901f0ce0ad.js/usr/local/bin/phantomjs 803f4b95012b22b1da8e4b4fbdf501901f0ce0ad.jsfinished

### phantomjs

[phantomjs](http://phantomjs.org/)

Programmatically capture web contents, including SVG and Canvas. Create web site screenshots with thumbnail preview.

[phantomjs screen capture](http://phantomjs.org/screen-capture.html)


### shortcode

    [pqshot site='https://www.youtube.com' width="234" sh="768" sw="1024" ct="0" cl="0" cw="1024" ch="768" ext="png"]
    
1. site=url to capture
2. image display=width
3. viewport size		sh=height  sw=width
4. clip rectangle		ct=top cl=left cw=width ch=height
5. file extension		ext=jpeg png gif
    
    <img class="screenshot_img" alt="https://www.youtube.com" width="234" src="http://example.com.au/pshots/p1/index.php?https%3A%2F%2Fwww.youtube.com?w=234&amp;sh=768&amp;sw=1024&amp;ct=0&amp;cl=0&amp;cw=1024&amp;ch=768&amp;ext=png">


