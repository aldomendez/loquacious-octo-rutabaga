<?php
ini_set('display_errors','off');
ini_set('date.timezone', 'America/Mexico_City');
error_reporting(E_ALL ^ E_NOTICE);
/**
* Abstrae la informacion de las bases de datos
*/		
class DataBase
{
	
	function __construct($db_name)
	{
		$this->name = $db_name;
		$this->oci_error_message = '';
	}

	public function name()
	{
		return $this->name;
	}
	public function connection($user, $password, $database)
	{
		$this->conn = oci_connect($user, $password, $database);
		if(!$this->conn){
			throw new Exception("Falla en la coneccion a la base de datos [" . $this->name . "]", 1);
		}
	}
	public function setQuery($query='')
	{
		$this->query = $query;
		$this->statement = oci_parse($this->conn, $query);
	}
	public function query($query='')
	{
		$this->setQuery($query);
		$this->exec();
	}
	public function insert($query='')
	{
		$this->setQuery($query);
		$this->only_exec();
	}
	public function bind($needle='',$haystack='')
	{
		oci_bind_by_name($this->statement, $needle, $haystack, -1);
	}
	public function only_exec()
	{
		if( oci_execute($this->statement) )
		{
			$this->state = TRUE;
		} else {
			$this->state = FALSE;
			$this->oci_error_message = oci_error($this->statement);
		}
	}
	public function exec()
	{
		// IDEA:--------------------------------------------------
		// Se me ocurre que esta parte de la aplicacion haga los
		// binds antes de lacer la ejecucion, y que tome un array
		// de binds de una variables del objeto.
		// -------------------------------------------------------
		if (oci_execute($this->statement)){
			$this->state = TRUE;
			$this->rows = oci_fetch_all($this->statement, $this->results, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
		} else {
			$this->state = FALSE;
			$this->oci_error_message = oci_error($this->statement);
		} 
	}
	public function numOfFields()
	{
		return oci_num_fields($this->statement);
	}

	public function fieldName($i)
	{
		return oci_field_name($this->statement, $i);
	}

	public function json()
	{
		return json_encode($this->results);
	}

	public function tabla()
	{
		// Inicializa la tabla
		$tabla ="";
		// empieza a escribir la cabecera de la tabla
		$tabla .= "<table class='table table-bordered table-condensed table-hover'><thead><tr>\n";
		// Crea los titulos
		for ($i=1; $i <= $this->numOfFields(); $i++){ 
			$tabla .= "<th>". $this->fieldName($i)."</th>"; 
		}
		// Crea el cuerpo
		$tabla .= "</tr></thead><tbody>";
		foreach ($this->results as $row) {
			$tabla .= "<tr>\n";
			foreach ($row as $key => $value) {
				$tabla .= " <td>" . ($value !== null ? htmlentities($value, ENT_QUOTES) : "&nbsp;") . "</td>\n";
			}
			$tabla .= "</tr>\n";
		}
		// Cierra todo lo hecho
		$tabla .= "</tbody></table>\n";
		// Escribe la tabla en la salida
		return $tabla;
	}
}

/**
* Clase especifica para generar conexiones a MXOPTIX
*/
class MxOptix extends DataBase
{
	
	function __construct()
	{
		$this->name = "MxOptix";
		$this->connection('phase2', 'g4it2day', 'MXOPTIX');
	}
}

/**
* Clase que genera conexiones a PROD_MX
*/
class Prod extends DataBase
{
	
	function __construct()
	{
		$this->name = "Prod";
		$this->connection('wp_db', 'wp1', 'PROD_MX');
	}
}

/**
* Clase que genera conexiones a MxApps
*/
class MxApps extends DataBase
{
	
	function __construct()
	{
		$this->name = "MxApps";
		$this->connection('apogee', 'eegopa1928', 'mxapps');
	}
}

/**
* Clase que genera conexiones a prod
*/
class Prod_ilm extends DataBase
{
	
	function __construct()
	{
		$this->name = "prod.world";
		$this->connection('ilm', 'i1m', 'prod');
	}
}

/**
* Clase que genera conexiones a prod
* no funciona!!!!!
* mi proveedor no tiene lo necesario para hacer las conecciones de esta manera
* estoy Buscando una forma alterna de hacerlo funcionar
*/
class Sfdm extends DataBase
{
	
	function __construct()
	{
		$this->name = "mzsfdmp";
		$this->connection('query', 'query', 'mzsfdmp.world');
	}
}

/**
* Clase que genera conexiones a prod
*/
class SAP extends DataBase
{
	
	function __construct()
	{
		$this->name = "ECP";
		$this->connection('optorpts', 'accessecp', '//10.114.130.10:1521/ECP');
	}
}