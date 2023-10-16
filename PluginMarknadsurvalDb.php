<?php
class PluginMarknadsurvalDb{
  private $settings = null;
  private $mysql = null;
  function __construct() {
    $this->settings = wfPlugin::getPluginSettings('marknadsurval/db', true);
    $this->mysql =new PluginWfMysql();
  }
  public function has_settings(){
    if($this->settings->get('settings/mysql')){
      return true;
    }else{
      return false;
    }
  }
  public function marknadsurval_cupdate_insert($data){
    $data['id'] = wfCrypt::getUid();
    $this->db_open();
    $sql = new PluginWfYml('/plugin/marknadsurval/db/sql/sql.yml', 'marknadsurval_cupdate_insert');
    $sql->setByTag($data, 'rs', true);
    $this->mysql->execute($sql->get());
    return null;
  }
  public function db_open(){
    $this->mysql->open($this->settings->get('settings/mysql'));
  }
  public function on_auth(){
    /**
     * check
     */
    $check = $this->db_marknadsurval_cupdate_select_by_pid(wfUser::getSession()->get('plugin/banksignering/ui/pid'));
    if($check->get('id') && $check->get('created_at_days')<180){
      return null;
    }
    /**
     * 
     */
    wfPlugin::includeonce('marknadsurval/api');
    $marknadsurval_api = new PluginMarknadsurvalApi();
    $marknadsurval_api->get_cupdate(wfUser::getSession()->get('plugin/banksignering/ui/pid'));
    /**
     * 
     */
    return null;
  }
  public function db_marknadsurval_cupdate_select_by_pid($pid){
    $this->db_open();
    $sql = new PluginWfYml('/plugin/marknadsurval/db/sql/sql.yml', __FUNCTION__);
    $sql->setByTag(array('pid' => $pid));
    $this->mysql->execute($sql->get());
    $rs = $this->mysql->getOne(array('sql' => $sql->get()));
    /**
     * 
     */
    wfPlugin::includeonce('validate/pid');
    $validate_pid = new PluginValidatePid();
    $validate_data = $validate_pid->isPid($rs->get('pid'));
    $rs->set('validate_born', $validate_data->get('born'));
    $rs->set('validate_sex', substr((string)$validate_data->get('sex'), 0, 1));
    /**
     * 
     */
    return $rs;
  }
}