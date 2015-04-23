<?php
/**
 * @file FrxEmailMerge
 * Email merge template.
 */
class FrxEmailMerge extends FrxRenderer {
  public $templateName = 'Mail Merge';
  public $email_input_format;

  public function __construct() {
    parent::__construct();
    $this->email_input_format = variable_get('forena_email_input_format', filter_default_format());
    $this->doc_types = array('email');
  }

  public function scrapeConfig() {
    $config=array();
    $config['class'] = get_class($this);
    $config['from'] = html_entity_decode($this->extractXPathInnerHTML("*//*[@class='email-header-from']", $this->reportDocDomNode));
    $config['to']= html_entity_decode($this->extractXPathInnerHTML("*//*[@class='email-header-to']", $this->reportDocDomNode));
    $config['cc'] = html_entity_decode($this->extractXPathInnerHTML("*//*[@class='email-header-cc']", $this->reportDocDomNode));
    $config['bcc'] = html_entity_decode($this->extractXPathInnerHTML("*//*[@class='email-header-bcc']", $this->reportDocDomNode));
    $config['subject'] =  $this->extractXPathInnerHTML("*//*[@class='email-header-subject']", $this->reportDocDomNode);
    $config['body']['value'] =  $this->extractXPathInnerHTML("*//*[@class='email-body']", $this->reportDocDomNode);
    return $config;
  }

  public function configForm($config) {

    $form_ctl['from'] = array(
      '#type' => 'textfield',
      '#title' => t('From'),
      '#default_value' => @$config['from'],
    );

    $form_ctl['to'] = array(
      '#type' => 'textfield',
      '#title' => t('To'),
      '#default_value' => @$config['to'],
    );

    $form_ctl['cc'] = array(
      '#type' => 'textfield',
      '#title' => t('Cc'),
      '#default_value' => @$config['cc'],
    );

    $form_ctl['bcc'] = array(
        '#type' => 'textfield',
        '#title' => t('Bcc'),
        '#default_value' => @$config['bcc'],
    );

    $form_ctl['subject'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#default_value' => @$config['subject'],
    );

    $form_ctl['body'] = array(
      '#type' => 'text_format',
      '#title' => t('Message'),
      "#format" => $this->email_input_format,
      '#default_value' => @$config['body']['value'],
    );
    return $form_ctl;
  }

  public function configValidate(&$config) {
    $this->validateTextFormats($config, array('body'));
  }

  public function generate($xml, &$config) {
    $from = @$config['from'];
    $to = @$config['to'];
    $cc = @$config['cc'];
    $bcc = @$config['bcc'];
    $subject = @$config['subject'];
    $body = @$config['body'];
    $body = $body['value'];
    $config['foreach'] = "*";
    $div = $this->blockDiv($config);
    $this->removeChildren($div);
    $doc = $this->addNode($div, 4, 'div', NULL, array('class' => 'email-document'));
    $header = $this->addNode($doc, 6, 'div', NULL, array('class' => 'email-header'));
    $table = $this->addNode($header, 8, 'table');
    $tr = $this->addNode($table, 10, 'tr');
    $td = $this->addNode($tr, 12, 'th', 'From');
    $td = $this->addNode($tr, 12, 'td', htmlentities($from), array('class' => 'email-header-from'));
    $tr = $this->addNode($table, 10, 'tr');
    $td = $this->addNode($tr, 12, 'th', 'To');
    $td = $this->addNode($tr, 12, 'td', htmlentities($to), array('class' => 'email-header-to'));
    if ($cc) {
      $tr = $this->addNode($table, 10, 'tr');
      $td = $this->addNode($tr, 12, 'th', 'Cc');
      $td = $this->addNode($tr, 12, 'td', htmlentities($cc), array('class' => 'email-header-cc'));
    }
    if ($bcc) {
      $tr = $this->addNode($table, 10, 'tr');
      $td = $this->addNode($tr, 12, 'th', 'Bcc');
      $td = $this->addNode($tr, 12, 'td', htmlentities($bcc), array('class' => 'email-header-bcc'));
    }

    $tr = $this->addNode($table, 10, 'tr');
    $td = $this->addNode($tr, 12, 'th', 'Subject');
    $td = $this->addNode($tr, 12, 'td', $subject, array('class' => 'email-header-subject'));
    $email_body = $this->addNode($doc, 6, 'div', NULL, array('class' => 'email-body'));
    $p = $this->addFragment($email_body, $body);
  }
}