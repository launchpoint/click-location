<?

class Location
{
  function __construct()
  {
    if (!array_key_exists('user_location', $_SESSION))
    {
      $this->set_from_ip();
      if (!isset($this->zip)) $this->set_from_zip('89507');
      $this->save();
    }
    $obj = $_SESSION['user_location'];
    foreach($obj as $k=>$v) $this->$k = $v;  
  }
  
  function set_from_ip()
  {
    $this->reset();
    list($a,$b,$c,$d) = preg_split("/\\./",$_SERVER['REMOTE_ADDR']);
    $ip = ip2long("$a.$b.$c.0");
    $rec = query_assoc("select * from ip_geocodes g join cities c on g.city_id = c.id where ip=$ip");
    if (count($rec)>=1)
    {
      $rec = $rec[0];
      $rec['name'] = urldecode($rec['name']);
      $this->area = $rec['name'];
      $this->lat = $rec['lat'];
      $this->lng = $rec['lng'];
      $this->zip = null;
      $sql = <<<SQL
    SELECT *,
          truncate((degrees(acos(
                  sin(radians(latitude))
                  * sin( radians({$this->lat}))
                  + cos(radians(latitude))
                  * cos( radians({$this->lat}))
                  * cos( radians(longitude - {$this->lng}) ) 
                  ) ) * 69.09),1) as distance
          FROM zip_codes
          ORDER BY distance
          limit 1
SQL;
      $rec = query_assoc($sql);
      if (count($rec)>=0)
      {
        $this->zip = $rec[0]['zip_code'];
      }
    }
    else
    {
      $this->set_from_zip('89507');
    }
    $this->save();
  }
  
  function set_from_zip($zip)
  {
    $this->reset();
    $res = query_assoc("select * from zip_codes where zip_code = '$zip' limit 1");
    if (count($res)>0)
    {
      $res = $res[0];
      $this->lat = $res['latitude'];
      $this->lng = $res['longitude'];
      $this->area = "{$res['city']}, {$res['state']}"; 
      $this->zip = $zip;
    } else {
      if ($zip!='89507') $this->set_from_zip('89507');
    }
    $this->save();
  }
  
  function save()
  {
    $_SESSION['user_location'] = $this;
  }
  
  function reset()
  {
    $this->lat=null;
    $this->lng=null;
    $this->area=null;
  }
  
  function get_closest($table_name, $miles = 30, $conditions =null)
  {
    if (!isset($this->zip)) return array();
    if(!$conditions)
    {
    $sql = <<<SQL
      select p.id, d.distance from $table_name p join
      (
        SELECT distinct zip_code,
              truncate((degrees(acos(
                      sin(radians(latitude))
                      * sin( radians({$this->lat}))
                      + cos(radians(latitude))
                      * cos( radians({$this->lat}))
                      * cos( radians(longitude - {$this->lng}) ) 
                      ) ) * 69.09),1) as distance
              FROM zip_codes
      ) d on d.zip_code = p.zip
            WHERE distance < $miles
            ORDER BY distance ASC
SQL;
    }
    else
    {
      $sql=<<<SQL
      select p.id, d.distance from $table_name p join
      (
        SELECT distinct zip_code,
              truncate((degrees(acos(
                      sin(radians(latitude))
                      * sin( radians({$this->lat}))
                      + cos(radians(latitude))
                      * cos( radians({$this->lat}))
                      * cos( radians(longitude - {$this->lng}) ) 
                      ) ) * 69.09),1) as distance
              FROM zip_codes
      ) d on d.zip_code = p.zip
            WHERE distance < $miles
            AND $conditions
            ORDER BY distance ASC
SQL;
    }
    $res = query_assoc($sql);
    $distances = array();
    foreach($res as $rec)
    {
      $distances[$rec['id']] = $rec['distance'];
    }
    if(count($distances)==0) return array();
    $ids = join(',',array_keys($distances));
    $klass = classify(singularize($table_name));
    $objs = eval("return $klass::find_all( array('conditions'=>array('id in (!)', \$ids)));");
    foreach($objs as $obj)
    {
      $obj->distance = $distances[$obj->id];
    }
    return $objs;
  }  
}