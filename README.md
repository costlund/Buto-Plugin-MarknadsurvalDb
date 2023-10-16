# Buto-Plugin-MarknadsurvalDb
## Settings
### Database
```
plugin:
  marknadsurval:
    db:
      settings:
        mysql: 'yml:/../buto_data/demokrationline/mysql_[host].yml'
```

## Usage

### Get data from db
```
wfPlugin::includeonce('marknadsurval/db');
$marknadsurval_db = new PluginMarknadsurvalDb();
if($marknadsurval_db->has_settings()){
  $marknadsurval_data = $marknadsurval_db->db_marknadsurval_cupdate_select_by_pid(wfRequest::get('pid'));
  wfHelp::print($marknadsurval_data);
}
```
Returns.
```
id: 604495508652d4dcb7554c154857216
pid: '198804214608'
first_name: 'MARIE SUSANNE'
given_name: MARIE
surname: Nilsson
address: 'Storgatan 6'
zip: '12345'
city: London
moved_at: null
status: ok
created_at: '2023-10-16 16:50:51'
updated_at: null
created_by: isdf8ds8fds8sdf8
updated_by: null
created_at_days: 0
validate_born: '1988-04-21'
validate_sex: F
```

### Banksignering
Add method on_auto to get api data when to sign in.
```
plugin:
  banksignering:
      auth:
        success:
          methods:
            -
              plugin: marknadsurval/db
              method: on_auth
```

