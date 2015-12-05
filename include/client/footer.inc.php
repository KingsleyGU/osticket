        </div>
    </div>
    <div id="footer">
        <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'osTicket.com'; ?> - All rights reserved.</p>
        <a id="poweredBy" href="http://osticket.com" target="_blank"><?php echo __('Helpdesk software - powered by osTicket'); ?></a>
    </div>
<div id="overlay"></div>
<div id="loading">
    <h4><?php echo __('Please Wait!');?></h4>
    <p><?php echo __('Please wait... it will take a second!');?></p>
</div>
<?php
if (($lang = Internationalization::getCurrentLanguage()) && $lang != 'en_US') { ?>
    <script type="text/javascript" src="ajax.php/i18n/<?php
        echo $lang; ?>/js"></script>
<?php } ?>
</body>
<script>
//Tell the window to respond to the width, along with the whole middle part.
jQuery("div#container").css("width","100%");// dynamic width to fit into the screen.
jQuery("table").attr('width', "100%"); //now that that has been resized, we need to make sure the th's fit within it.
jQuery(".message th:nth-child(1)").attr('width',"20%");

jQuery(".message th:nth-child(2)").attr('width',"30%");
// jQuery(".message th:nth-child(2)").attr('height',"200px");
jQuery(".message th:nth-child(3)").attr('width',"30%");
// jQuery(".message th:nth-child(3)").attr('height',"200px");
jQuery(".message th:nth-child(4)").attr('width',"20%");
jQuery(".message th:nth-child(4)").attr('height',"200px");
</script>
</html>
