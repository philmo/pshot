
# pshot

## Overview

pshot is small implementation of the mshot_class [automatic/mshots](https://github.com/Automattic/mshots) to capture web images and process server-side. It uses phantomjs to capture images using its render feature. The processing of images is queued to the server using interprocess messaging provided through php semaphores.[creating-a-message-queue-in-php](http://www.ebrueggeman.com/blog/creating-a-message-queue-in-php).



## Installation

1. install phantomjs
2. configure queue - qworker
3. create pshot working directory on webserver    default to ../pshots/p1
4. add pshot shortcode to wordpress or call direct within html


## Details

### pshot class

define constants for location to store thumbnails and default gif

    const location_base = '/var/www/html/static/thumbnails';
    const snapshot_default = 'http://example.com.au/pshots/default.gif';

### queue workers

cron job to start worker
modify install location in worker.php to suit

    * * * * * sh /home/qworker/manager.sh


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
    


