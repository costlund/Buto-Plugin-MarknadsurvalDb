<?php
class PluginMarknadsurvalDb{
  private $settings = null;
  private $mysql = null;
  function __construct() {
    $this->settings = wfPlugin::getPluginSettings('marknadsurval/db', true);
    wfPlugin::includeonce('wf/mysql');
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
    /**
     * log
     */
    $log = new PluginWfYml(wfGlobals::getAppDir().'/../buto_data/theme/[theme]/plugin/marknadsurval/db/'.date('Y-m-d').'.yml');
    $log->set('log/', array('time' => date('Y-m-d H:i:s'), 'pid' => wfUser::getSession()->get('plugin/banksignering/ui/pid'), 'result' => $check->get()));
    $log->set('count', sizeof($log->get('log')));
    $log->save();
    /**
     * 
     */
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
     * pid
     */
    if(!$rs->get('pid')){
      $rs->set('pid', $pid);
    }
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
  public function set_user(){
    /**
     * Run this method via theme settings.
     */
    wfPlugin::includeonce('marknadsurval/db');
    $marknadsurval_db = new PluginMarknadsurvalDb();
    if($marknadsurval_db->has_settings()){
      $marknadsurval_data = $marknadsurval_db->db_marknadsurval_cupdate_select_by_pid(wfUser::getSession()->get('plugin/banksignering/ui/pid'));
      $data = new PluginWfArray();
      $data->set('id', $marknadsurval_data->get('id'));
      $data->set('pid', $marknadsurval_data->get('pid'));
      $data->set('postalcode', $marknadsurval_data->get('zip'));
      $data->set('city', $marknadsurval_data->get('city'));
      $data->set('first_name', $marknadsurval_data->get('given_name'));
      $data->set('last_name', $marknadsurval_data->get('surname'));
      $data->set('address', $marknadsurval_data->get('address'));
      $data->set('born', $marknadsurval_data->get('validate_born'));
      $data->set('sex', $marknadsurval_data->get('validate_sex'));
      $data->set('created_at', $marknadsurval_data->get('created_at'));
      $data->set('created_at_days', $marknadsurval_data->get('created_at_days'));
      wfUser::setSession('plugin/marknadsurval/db/user', $data->get());
   }
    return null;
  }
}