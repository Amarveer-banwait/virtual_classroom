<?php
$famousBirthdays = FAMBDAY_get_instance();
?>
<div class="wrap">
  <h2>Famous Birthdays Settings</h2>
  <form action="options-general.php?page=fambday" method="post">
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">Widget Text</strong></th>
          <td>
            <label>
              <input type="text" size="50" value="<?php echo $famousBirthdays->settings->title; ?>" name="fambday_text"/>
            </label>
          </td>
        </tr>
        <tr>
          <th scope="row">Date Text Color:</strong></th>
          <td>
            <input type="text" name="fambday_date_color" value="<?php echo $famousBirthdays->settings->date_text_color; ?>" class="fambday-color-field" data-default-color="#FFFFFF" />
          </td>
        </tr>
        <tr>
          <th scope="row">Primary Text Color</strong></th>
          <td>
            <input type="text" name="fambday_text_color" value="<?php echo $famousBirthdays->settings->primary_text_color; ?>" class="fambday-color-field" data-default-color="#000000" />
          </td>
        </tr>
        <tr>
          <th scope="row">Widget Header Background Color</strong></th>
          <td>
            <input type="text" name="fambday_header_color" value="<?php echo $famousBirthdays->settings->header_background_color; ?>" class="fambday-color-field" data-default-color="#4c90af" />
          </td>
        </tr>
        <tr>
          <th scope="row">Widget Body Background Color</strong></th>
          <td>
            <input type="text" name="fambday_body_color" value="<?php echo $famousBirthdays->settings->body_background_color; ?>" class="fambday-color-field" data-default-color="#FFFFFF" />
          </td>
        </tr>
      </tbody>
    </table>
    <p><input type="submit" name="fambday_submit" value="Save Settings" class="button-primary"/></p>
  </form>
</div>
