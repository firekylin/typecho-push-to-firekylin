<?php
/**
 * Typecho Push2Firekylin
 *
 * @package PushToFireylin
 * @author lizheming
 * @version 0.0.1
 * @link https://github.com/firekylin/typecho-push-to-firekylin
 */

class PushToFirekylin_Plugin implements Typecho_Plugin_Interface {
  public static function active() {
    Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('PushToFirekylin_Plugin', 'push');
  }
  /* 禁用插件方法 */
  public static function deactivate(){}
  
  /* 个人用户的配置方法 */
  public static function personalConfig(Typecho_Widget_Helper_Form $form){}

  public static function config($form) {
    $siteName = new Typecho_Widget_Helper_Form_Element_Text('siteName', NULL, NULL, _t('网站名称'));
    $form->addInput(
      $siteName->addRule('required', _t('网站名称不能为空'))
    );

    $siteUrl = new Typecho_Widget_Helper_Form_Element_Text('siteUrl', NULL, 'http://', _t('网站地址'));
    $form->addInput(
      $siteUrl->addRule('required', _t('网站地址不能为空'))
    );

    $appKey = new Typecho_Widget_Helper_Form_Element_Text('appKey', NULL, '', _t('推送公钥'));
    $form->addInput(
      $appKey->addRule('required', _t('推送公钥不能为空'))
    );

    $appSecret = new Typecho_Widget_Helper_Form_Element_Text('appSecret', NULL, '', _t('推送密钥'));
    $form->addInput(
      $appSecret->addRule('required', _t('推送密钥不能为空'))
    );
  }

  public static function push($contents, $class) {
    $plugin = Typecho_Widget::widget('Widget_Options')->plugin('PushToFirekylin');
    $siteName = $plugin->siteName;
    $siteUrl = $plugin->siteUrl;
    $appKey = $plugin->appKey;
    $appSecret = $plugin->appSecret;

    if(is_null($siteUrl) || is_null($appKey) || is_null($appSecret)) {
      throw new Typecho_Plugin_Exception(_t('PushToFirekylin 推送配置未完全'));
    }

    if($contents['visibility'] != 'publish' || $contents['created'] > time()) {
      return;
    }

    $data = array(
      "title" => $contents['title'],
      "pathname" => $class->permalink,
      "markdown_content" => $contents['text'],
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$siteUrl/admin/post_push");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
    curl_close($ch);
  }
}