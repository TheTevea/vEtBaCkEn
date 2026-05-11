<?php
echo $this->element('prevent_multiple_submit');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("#UserLoginForm").validationEngine();
        // clear cookie
        $.cookie('cookieTitle', null, { expires: 7, path: "/" });
        $.cookie('cookieHref', null, { expires: 7, path: "/" });
        $.cookie('cookieTabIndex', null, { expires: 7, path: "/" });
        // Check & Focus
        if($("#UserUsername").val() != ''){
            $("#UserPassword").focus();
        } else {
            $("#UserUsername").focus();
        }
        
        $(".btnLogin").click(function(){
            var formName = "#UserLoginForm";
            var validateBack =$(formName).validationEngine("validate");
            if(!validateBack){
                return false;
            }else{
                $(".txtLogin").text('កំពុងដំណើរ ...');
                $(formName).submit();
            }
        });
    });
</script>
<?php echo $this->Form->create('User', array('action' => 'login')); ?>
<input type="hidden" id="lat" name="data[User][lat]" />
<input type="hidden" id="long" name="data[User][long]" />
<input type="hidden" id="accuracy" name="data[User][accuracy]" />
<?php
if(SERVER_TYPE != "1"){
?>
<div style="font-size: 14px;" id="divCheckServerDate">Checking server's date time...</div>
<div id="divChangeDate" style="display: none;">
    <table style="width: 100%;" cellpadding="5" cellspacing="0">
        <tr>
            <td colspan="2" style="text-align: center; font-size: 14px; font-weight: bold;">Change Server Date</td>
        </tr>
        <tr>
            <td><label for="">Server Date Time:</label></td>
            <td>
                <input type="text" id="changeServerDateTime" style="width: 150px; height: 30px;" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <div class="buttons">
                    <button type="submit" class="positive btnSaveServerDate">
                        <img src="<?php echo $this->webroot; ?>img/button/save.png" alt=""/>
                        <label id="lblSaveServerDate">Save</label>
                    </button>
                </div>
            </td>
        </tr>
    </table>
</div>
<?php
}
?>
<table cellpadding="2" cellspacing="0" id="login" width="100%">
    <tr>
        <td style="text-align: center;">
            <img alt="" src="<?php echo $this->webroot; ?>img/logo_s.png" style="width: 130px;" />
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"><?php echo $this->Session->flash(); ?></td>
    </tr>
    <tr>
        <th class="title" style="font-size: 24px;">ប្រព័ន្ធកក់សំបុត្រឡាន</th>
    </tr>
    <tr>
        <th class="title" style="font-size: 20px;">Booking Tickets System</th>
    </tr>
    <tr>
        <th class="title" style="padding-top: 40px;">ចូលប្រើប្រាស់</th>
    </tr>
    <tr>
        <td><label for="UserUsername" style="margin-left: 15px; font-size: 12px;" class="title">ឈ្មោះ​អ្នកប្រើប្រាស់:</label></td>
    </tr>
    <tr>
        <td style="text-align: center;"><input id="UserUsername" class="validate[required]" type="text" name="data[User][username]" style="height: 30px; width: 90%;" /></td>
    </tr>
    <tr>
        <td><label for="UserPassword" style="margin-left: 15px; font-size: 12px;" class="title">ពាក្យសម្ងាត់:</label></td>
    </tr>
    <tr>
        <td style="text-align: center;"><input id="UserPassword" class="validate[required]" type="password" name="data[User][password]" style="height: 30px; width: 90%;" /></td>
    </tr>
    <?php if ($log >= 3) { ?>
    <tr>
        <td style="text-align: center;">
            <div style="width: 300px;display: inline-block;">
                <img alt="" id="secret" align="left" style="border: 0;" src="captcha/securimage_show_example.php?sid=<?php echo md5(time()) ?>" />
                <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="19" height="19" id="SecurImage_as3" align="middle">
                    <param name="allowScriptAccess" value="sameDomain" />
                    <param name="allowFullScreen" value="false" />
                    <param name="movie" value="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" />
                    <param name="quality" value="high" />
                    <param name="bgcolor" value="#ffffff" />
                    <param name="wmode" value="transparent" />
                    <embed src="captcha/securimage_play.swf?audio=captcha/securimage_play.php&bgColor1=#777&bgColor2=#fff&iconColor=#000&roundedCorner=5" quality="high" bgcolor="#ffffff" width="19" height="19" name="SecurImage_as3" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" wmode="transparent" />
                </object>
                <br />
                <a style="border-style: none" href="#" title="Refresh Image" onclick="document.getElementById('secret').src = 'captcha/securimage_show_example.php?sid=' + Math.random(); return false"><img src="<?php $this->webroot; ?>captcha/images/refresh.png" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" /></a>
            </div>
            <div class="clearer"></div>
        </td>
    </tr>
    <tr>
        <td align="right"><label for="UserCode">លេខ​កូដសុ​វត្ថ​ភាព:</label></td>
        <td><?php echo $form->text('code'); ?></td>
    </tr>
    <?php } ?>
    <tr>
        <td>
            <div class="buttons" style="margin-top: 10px; margin-left: 40px;">
                <button type="submit" class="positive btnLogin" style="width: 250px; height: 50px;">
                    <img src="<?php echo $this->webroot; ?>img/button/textfield_key.png" alt=""/>
                    <span class="txtLogin">ចូល</span>
                </button>
            </div>
        </td>
    </tr>
</table>
<?php echo $this->Form->end(); ?>