<?php
  class EFieldException extends Exception {};
  
  class CodeSense_mysqli extends mysqli
  {
    public $last_inserted_id = null;
    
    public function prepare($sql)
    {
       if (!$Query = parent::prepare($sql))
        throw new Exception($this->error);
      return $Query;
    }
    
    private function LoadFieldInfo($ResultSet)
    {
      $metadata = $ResultSet->result_metadata();
      if ($metadata != null) {
          $fields = $metadata->fetch_fields();
          $metadata->close();
      } else $fields = array();
      return $fields;
    }

    public static function CreateNew($username, $password, $database, $servername = 'localhost')
    {
      $server = new CodeSense_mysqli($servername, $username, $password, $database);
      $server->ExecuteSQL("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
      return $server; 
    }
    
    /*
     * Executes an UPDATE, INSERT or DELETE statement on the database.
     * Returns the insert_id, where relevant, or null.
     */
    public function ExecuteSQL($sql, $paramtypes = null)
    {
      $query = $this->prepare($sql);
      if (isset($paramtypes))
      {
        $params = array(0 => $paramtypes);
        $paramcount = func_num_args();
        for ($i = 2; $i < $paramcount; $i++)
          $params[] = func_get_arg($i);
        
        if (!call_user_func_array(array($query, 'bind_param'), $params))
          die("Bind parameters failed");
      }
      $query->execute();
      $result = ($query->insert_id > 0)?$query->insert_id:null;
      //echo("last ID:".$query->insert_id);
      if ($query->insert_id > 0) {
          //echo("SAVED last ID:".$query->insert_id);
          $this->last_inserted_id = $query->insert_id;
      }
      $query->close();
      return $result;
    }
    
    /*
     * Executes a (parameterised) select statement on the database.
     * Returns a single value, or an array of values if multiple fields are requested.
     * Closes the DB query handle before exiting.
     */
    public function FetchRow($sql, $paramtypes = null)
    {
      $vars = array();
      $bindparams = array();
      
      $query = $this->prepare($sql);
      if (isset($paramtypes))
      {
        $params = array(0 => $paramtypes);
        $paramcount = func_num_args();
        for ($i = 2; $i < $paramcount; $i++)
          $params[] = func_get_arg($i);
          
        if (!call_user_func_array(array($query, 'bind_param'), $params))
          die("Bind parameters failed");
      }
      $query->execute();
      $fields = $this->LoadFieldInfo($query);

      foreach ($fields as $idx => $field)
      {
        $fieldname = $field->name;
        $vars[$fieldname] = null;
        $bindparams[$idx] =& $vars[$fieldname];
      }
      
      if (!call_user_func_array(array($query, 'bind_result'), $bindparams))
        die("bind_result failed");
      
      $result = array();
      $query->fetch();
      $query->close();
      
      return (count($fields) === 1)?$bindparams[0]:$bindparams;
    }

    public function FetchAll($sql, $paramtypes = null)
    {
      $vars = array();
      $bindparams = array();

      $query = $this->prepare($sql);
      if (isset($paramtypes))
      {
        $params = array(0 => $paramtypes);
        $paramcount = func_num_args();
        for ($i = 2; $i < $paramcount; $i++)
          $params[] = func_get_arg($i);

        if (!call_user_func_array(array($query, 'bind_param'), $params))
          die("Bind parameters failed");
      }
      $query->execute();
      $fields = $this->LoadFieldInfo($query);

      foreach ($fields as $field)
      {
        $fieldname = $field->name;
        $vars[$fieldname] = null;
        $bindparams[$fieldname] =& $vars[$fieldname];
      }
      
      if (!call_user_func_array(array($query, 'bind_result'), $bindparams))
        die("bind_result failed");
      
      $result = array();
      while ($query->fetch())
      {
        $obj = array(); //new ResultSetObject();
        foreach ($fields as $field)
        {
          $fieldname = $field->name;
          $obj[$fieldname] = $vars[$fieldname];
        }
        $result[] = $obj;
      }
      $query->close();
      
      return $result;
    }
  }
?>
