<?php 
// Prevent Button Submit
echo $this->element('prevent_multiple_submit'); ?>
<script type="text/javascript">
  $(document).ready(function(){
      // Prevent Key Enter
      preventKeyEnter();
      $("#CouponAddForm").validationEngine('attach', {
          isOverflown: true,
          overflownDIV: ".ui-tabs-panel"
      });
      // Numeric and Datepicker initialization
      $(".interger").autoNumeric({mDec: 0, aSep: ','});
      $(".float").autoNumeric({mDec: 2, aSep: ','});

      var dates = $("#CouponStart, #CouponEnd").datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            onSelect: function( selectedDate ) {
                var option = this.id == "CouponStart" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
      function updateCodeModeUI(){
          var mode = $('input[name="data[Coupon][code_mode]"]:checked').val() || 'prefix';
          if(mode === 'exact'){
              $('.row-prefix').hide();
              $('.row-prefix-note').hide();
              $('.row-exact').show();
              $('.row-exact-note').show();
              // Update validation classes and asterisks
              $('#CouponCodePrefix').removeClass('validate[required]');
              $('#CouponTotalGenerate').removeClass('validate[required]');
              $('#CouponExactCode').addClass('validate[required]');
              $('.code-prefix-required').hide();
              $('.total-generate-required').hide();
              $('.exact-code-required').show();
          } else {
              $('.row-prefix').show();
              $('.row-prefix-note').show();
              $('.row-exact').hide();
              $('.row-exact-note').hide();
              // Update validation classes and asterisks
              $('#CouponCodePrefix').addClass('validate[required]');
              $('#CouponTotalGenerate').addClass('validate[required]');
              $('#CouponExactCode').removeClass('validate[required]');
              $('.code-prefix-required').show();
              $('.total-generate-required').show();
              $('.exact-code-required').hide();
          }
      }
      $('input[name="data[Coupon][code_mode]"]').on('change', updateCodeModeUI);
      // Initialize UI state
      updateCodeModeUI();
      $("#CouponAddForm").ajaxForm({
          beforeSerialize: function($form, options){
              // Strip formatting from numeric fields
              $(".float, .interger").each(function(){
                  $(this).val($(this).val().replace(/,/g, ""));
              });
              $("#CouponStart, #CouponEnd").datepicker("option", "dateFormat", "yy-mm-dd");
              // Clear values from the non-selected mode to avoid server-side ambiguity
              var mode = $('input[name="data[Coupon][code_mode]"]:checked').val() || 'prefix';
              if(mode === 'exact'){
                  $("#CouponCodePrefix").val('');
                  $("#CouponTotalGenerate").val('');
                  $("#CouponStartNumber").val('');
              } else {
                  $("#CouponExactCode").val('');
              }
          },
          beforeSubmit: function(arr, $form, options) {
              $(".txtSaveCoupon").html("<?php echo ACTION_LOADING; ?>");
              $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner.gif");
          },
          success: function(result) {
              $(".loader").attr("src", "<?php echo $this->webroot; ?>img/layout/spinner-placeholder.gif");
              $(".btnBackCoupon").click();
              // alert message
              if(result != '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>' && result != '<?php echo MESSAGE_DATA_COULD_NOT_BE_SAVED; ?>'){
                  createSysAct('Coupon', 'Add', 2, result);
                  $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span><?php echo MESSAGE_PROBLEM; ?></p>');
              }else {
                  createSysAct('Coupon', 'Add', 1, '');
                  // alert message
                  $("#dialog").html('<p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>'+result+'</p>');
              }
              var isSaved = (result == '<?php echo MESSAGE_DATA_HAS_BEEN_SAVED; ?>');
              var dialogButtons = {};
              if(isSaved){
                  // Auto-export only the last created coupons
                //   $.post("<?php echo $this->webroot; ?>coupons/exportExcel", { action: 'export', only_last_created: 1 })
                //    .always(function(){
                //       window.location = "<?php echo $this->webroot; ?>public/report/coupon_export.csv";
                //    });

                  // Provide a button to re-download the same set
                  dialogButtons['<?php echo __("Export to Excel", true); ?>'] = function(){
                      $.post("<?php echo $this->webroot; ?>coupons/exportExcel", { action: 'export', only_last_created: 1 })
                       .always(function(){
                          window.location = "<?php echo $this->webroot; ?>public/report/coupon_export.csv";
                       });
                  };
              }
              dialogButtons['<?php echo ACTION_CLOSE; ?>'] = function() { $(this).dialog("close"); };
              $("#dialog").dialog({
                  title: '<?php echo DIALOG_INFORMATION; ?>',
                  resizable: false,
                  modal: true,
                  width: 'auto',
                  height: 'auto',
                  open: function(event, ui){ $(".ui-dialog-buttonpane").show(); },
                  buttons: dialogButtons
              });
          }
      });
      $(".btnBackCoupon").click(function(event){
          event.preventDefault();
          oCache.iCacheLower = -1;
          oTableCoupon.fnDraw(false);
          var rightPanel=$(this).parent().parent().parent();
          var leftPanel=rightPanel.parent().find(".leftPanel");
          rightPanel.hide();rightPanel.html("");
          leftPanel.show("slide", { direction: "left" }, 500);
      });
  });
  </script>
  <div style="padding: 5px;border: 1px dashed #bbbbbb;">
      <div class="buttons">
          <a href="" class="positive btnBackCoupon">
              <img src="<?php echo $this->webroot; ?>img/button/left.png" alt=""/>
              <?php echo ACTION_BACK; ?>
          </a>
      </div>
      <div style="clear: both;"></div>
  </div>
  <br />
  <?php echo $this->Form->create('Coupon'); ?>
<fieldset>
    <legend><?php __(MENU_COUPON_ADD); ?></legend>
    <table cellpadding="5px">
        <tr>
            <td><label for="CouponCodeMode"><?php echo __('Code Entry Mode', true); ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->radio('code_mode', array('prefix' => __('Code Prefix', true), 'exact' => __('Exact Code', true)), array('legend' => false, 'value' => 'prefix')); ?>
                </div>
                <br/>
                <div class="row-prefix-note" style="font-style: italic; color: #555; padding-top: 5px;">
                    Use this to generate multiple coupons. Enter a prefix (e.g., "SALE2024") and the total number to create.
                </div>
                <div class="row-exact-note" style="font-style: italic; color: #555; padding-top: 5px;">
                    Use this to create a single coupon with a specific name (e.g., "VIPGIFT").
                </div>
            </td>
        </tr>
        <tr class="row-prefix">
            <td><label for="CouponCodePrefix"><?php echo TABLE_CODE; ?> <span class="red code-prefix-required">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('code_prefix', array('placeholder'=>__('Code start from word', true))); ?>
                </div>
            </td>
        </tr>
        <tr class="row-exact">
            <td><label for="CouponExactCode"><?php echo __('Exact Code', true); ?> <span class="red exact-code-required">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('exact_code', array('placeholder'=>__('If set, generate only this code', true))); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CouponStart"><?php echo REPORT_FROM; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('start', array('class'=>'validate[required]', 'readonly'=>true)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CouponEnd"><?php echo REPORT_TO; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('end', array('class'=>'validate[required]', 'readonly'=>true)); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CouponAmount"><?php echo GENERAL_AMOUNT; ?> ($) <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('amount', array('class'=>'validate[required] float')); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CouponTotalTimeUse"><?php echo TABLE_TIME_USE; ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('total_time_use', array('class'=>'validate[required] interger')); ?>
                </div>
            </td>
        </tr>
        <tr class="row-prefix">
            <td><label for="CouponTotalGenerate"><?php echo __('Total Coupon to Generate', true); ?> <span class="red total-generate-required">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('total_generate', array('class'=>'interger', 'placeholder'=>__('Required if Exact Code is empty', true))); ?>
                </div>
            </td>
        </tr>
        <tr class="row-prefix">
            <td><label for="CouponStartNumber"><?php echo __('Start Number', true); ?> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->text('start_number', array('class'=>'interger', 'placeholder'=>__('Optional, default continues after existing', true))); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td><label for="CouponCouponTypeId"><?php echo __('Coupon Type', true); ?> <span class="red">*</span> :</label></td>
            <td>
                <div class="inputContainer">
                    <?php echo $this->Form->select('coupon_type_id', $couponTypes, null, array('empty'=>'-- Select --', 'class'=>'validate[required]')); ?>
                </div>
            </td>
        </tr>
    </table>
    <br/>
    <div class="buttons">
        <button type="submit" class="positive">
            <img src="<?php echo $this->webroot; ?>img/button/tick.png" alt=""/>
            <span class="txtSaveCoupon"><?php echo ACTION_SAVE; ?></span>
        </button>
    </div>
    <div style="clear: both;"></div>
</fieldset>
<?php echo $this->Form->end(); 
?>