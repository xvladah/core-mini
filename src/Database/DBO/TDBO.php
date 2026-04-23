<?php

	class TDBO extends TSQLBase
	{
        const string TABLE_NAME        = '';
        const array TABLE_FUNCTIONS   = [];
        const array TABLE_CONDITIONS  = [];
        const array TABLE_KEYS        = [];

        protected TPDO $pdo;

		final public static function getInstance(TPDO $pdo)
		{
			static $instances = [];

			$calledClass = get_called_class();

			if (!isset($instances[$calledClass]))
				$instances[$calledClass] = new $calledClass($pdo);
			else
				$instances[$calledClass]->pdo = $pdo;

			return $instances[$calledClass];
		}
		
		final public function __clone()
		{
		}

		public function __construct(TPDO $pdo)
		{
			$this->pdo = $pdo;
		}

        /**
         * Zakladni SELECT z databaze
         *
         * @param mixed $columns
         * @param array $where_params
         * @param array $order
         * @param int $offset
         * @param int $count
         * @param ?string $from
         * @return PDOStatement
         *
         * @throws ESQLBase
         * @example
         * SELECT uzi_prijmeni AS prijmeni FROM uzivatele WHERE uzi_pk_id > 10 ORDER BY uzi_prijmeni ASC
         *
         * $result = $query->select(['prijmeni'], ['>uzivatel_id'=>10], ['prijmeni'=>'ASC]);
         */
		public function select(mixed $columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): PDOStatement
        {
			$params = [];
			$turn = 1;
			$where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_select($columns, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			return $stms;
		}

		/**
		 * Funkce vraci pocet hodnot daneho sloupce $column, pokud je specifikovan, jinak se bere primarni klic
		 *
		 * @param array $where_params
		 * @param ?string $column
		 * @param ?string $from
		 * @return mixed
		 *
		 * @example
		 * SELECT COUNT(uzi_prijmeni) FROM uzivatele WHERE uzi_pk_id > 10
		 *
		 * $result = $query->count(['>uzivatel_id'=>10], 'prijmeni');
		 */
		public function count(array $where_params = [], ?string $column = null, ?string $from = null): mixed
        {
			$params = [];
			$turn = 1;
			$where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($column === null)
				$column = static::TABLE_KEYS[0];
			
			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_count($column, $where, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			return $stms->fetchColumn(0);
		}

		/**
		 * Funkce vraci pocet jedinecnych zaznamu sloupce $column, pokud je specifikovan, jinak se bere primarni klic tabulky
		 *
		 * @param array $where_params
		 * @param ?string $column
		 * @param ?string $from
		 * @return mixed
		 *
		 * @example
		 * SELECT COUNT(DISTINCT(uzi_pk_id)) FROM uzivatele WHERE uzi_pk_id > 10
		 *
		 * $result = $query->countDistinct(['>uzivatel_id'=>10]);
		 */
		public function countDistinct(array $where_params, ?string $column = null, ?string $from = null): mixed
        {
			$params = [];
			$turn = 1;
			$where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($column === null)
				$column = static::TABLE_KEYS[0];

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_countDistinct($column, $where, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			return $stms->fetchColumn(0);
		}

		/**
		 * Funkce smaze dane zaznamy z tabulky
		 *
		 * @param array $where_params
		 * @param ?string $from
		 * @return PDOStatement
		 *
		 * @example
		 * DELETE FROM uzivatele WHERE uzi_pk_id = 10
		 *
		 * $result = $query->delete(['uzivatel_id'=>10]);
		 */
		public function delete(array $where_params, ?string $from = null): PDOStatement
        {
			$params = [];
			$turn = 1;
			$where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_delete($where, $from);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			return $stms;
		}

		/**
		 * Funkce vrací hodnoty z agregační funkce
		 *
		 * @param mixed $columns
		 * @param array $where_params
		 * @param mixed $group
		 * @param array $order
		 * @param int $offset
		 * @param int $count
		 * @param ?string $from
		 * @return PDOStatement
		 *
		 * @example
		 * SELECT AVG(uzi_plat) AS plat_avg FROM uzivatele WHERE uzi_pk_id > 10 GROUP BY uzi_prijmeni ORDER BY uzi_prijmeni ASC
		 *
		 * $result = $query->group(['plat_avg'], 'prijmeni', ['>uzivatel_id'=>10], ['prijmeni'=>'asc']);
		 */
		public function group(array $columns, mixed $group, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): PDOStatement
        {
			$params = [];
			$turn = 1;
			$where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_group($columns, $group, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			return $stms;
		}

		/**
		 * Funkce vrací jednu hodnotu
		 *
		 * @param string $column
		 * @param array $where_params
		 * @param ?string $from
		 * @throws  EPDOBase
		 * @return string
		 *
		 * @example
		 * SELECT uzi_prijmeni AS prijmeni FROM uzivatele WHERE uzi_pk_id = 10
		 *
		 * $prijmeni = $query->column('prijmeni', ['uzivatel_id'=>10]);
		 */
		public function column(string $column, array $where_params, array $order = [], ?string $from = null): ?string
        {
			$params = [];
			$turn = 1;
			$where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_column($column, $where, $order, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			if($stms->rowCount() !== 1)
				throw new  EPDOBase('SQL COLUMN: Data not found', -300);

			return $stms->fetchColumn(0);
		}

		/**
		 * Funkce vrací hodnoty funkce se zadanými parametry
		 *
		 * @param string $fce
		 * @param string $column
		 * @param array $where_params
		 * $param ?string $from
		 * @throws  EPDOBase
		 * @return string
		 *
		 * @example
		 * SELECT AVG('uzi_plat') AS plat FROM uzivatele WHERE uzi_pk_id > 10
		 *
		 * $prumer = $query->column_fce('AVG', 'plat', ['>uzivatel_id'=>10]);
		 */
		public function column_fce(string $fce, string $column, array $where_params, ?string $from = null) :string
		{
			$params = [];
			$turn = 1;
			$where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_column_fce($fce, $column, $where, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			if($stms->rowCount() != 1)
				throw new  EPDOBase('SQL COLUMN_FCE: Data not found', -300);

			return $stms->fetchColumn(0);
		}

		/**
		 * Funkce vrací jeden záznam s hodnotami sloupců
		 *
		 * @param mixed $columns
		 * @param array $where_params
		 * @param ?string $from
		 * @throws  EPDOBase
		 * @return array
		 *
		 * @example
		 * SELECT uzi_prijmeni AS prijmeni, uzi_jmeno AS jmeno FROM uzivatele WHERE uzi_pk_id = 10
		 *
		 * $zaznam = $query->record(['prijmeni','jmeno'], ['uzivatel_id'=>10]);
		 */
		public function record($columns, array $where_params, array $order = [], ?string $from = null): array
        {
			$params = [];
			$turn = 1;

			$where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_select($columns, $where, $order, 0, 1, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, $this->LOGIC);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
			$stms->execute($params);

			if($stms->rowCount() != 1)
				throw new  EPDOBase('SQL RECORD: Data not found', -300);

			return $stms->fetch();
		}

		/**
		 * Funkce vkládá nové hodnoty do tabulky
		 *
		 * @param array $insert_params
		 * @return PDOStatement
		 *
		 * @example
		 * INSERT INTO uzivatele(uzi_prijmeni,uzi_jmeno') VALUES('Novak','Karel')
		 *
		 * $result = $query->insert(['prijmeni'=>'Novak', 'jmeno'=>'Karel']);
		 */
		public function insert(array $insert_params): PDOStatement
        {
			$columns = '';

			$params = [];
			$values = parent::parse_insert($insert_params, $columns, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			$sql = parent::_insert($columns, $values, static::TABLE_NAME);

			parent::logger($sql, $params);

			$stmt = $this->pdo->prepare($sql); //, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
			$stmt->execute($params);

			return $stmt;
		}

		public function insertMulti(array $multi_insert_params): bool
        {
			$columns 	= '';

			$params 	= [];
			$sql 		= '';

			foreach($multi_insert_params as $m => $insert_params)
			{
				$values = parent::parse_insert($insert_params, $columns, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, $m);
				if($sql == '')
					$sql = 'INSERT INTO '.static::TABLE_NAME.'('.$columns.')VALUES';
				else
					$sql .= ',';

				$sql .= '('.$values.')';
			}
			$sql .= ';';

			parent::logger($sql, $params);

			$stmt = $this->pdo->prepare($sql);
			$query = $stmt->execute($params);

			return $query;
		}

		/**
		 * Funkce provádí update hodnot nad tabulkou
		 *
		 * @param array $update_params
		 * @param array $where_params
		 * @return PDOStatement
		 *
		 * @example
		 * UPDATE uzivatele SET uzi_prijmeni = 'Novak' WHERE uzi_pk_id = 10
		 *
		 * $result = $query->update(['prijmeni'=>'Novak'], ['uzivatel_id'=>10]);
		 */
		public function update(array $update_params, array $where_params) :PDOStatement
		{
			$params  = [];
			$turn = 1;
			$where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);
			$set 	 = parent::parse_update($update_params, $params,  static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			$sql = parent::_update($set, $where, static::TABLE_NAME);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
			$stms->execute($params);

			return $stms;
		}

		/**
		 * Funkce vrací pole s klíčem a hodnotou
		 * Většinou použitelné do TComboBox jako hodnoty pro výběr
		 *
		 * @param array $columns
		 * @param array $options
		 * @param array $where_params
		 * @param array $order
		 * @param int $offset
		 * @param int $count
		 * @param ?string $from
		 * @return array
		 *
		 * @example
		 * SELECT uzi_pk_id AS uzivatel_id, uzi_prijmeni AS prijmeni FROM uzivatele WHERE uzi_pk_id > 10 ORDER BY uzi_prijmeni ASC
		 *
		 * $options = $query->options(['uzivatel_id'=>'prijmeni'], [''=>'Vše'], ['>uzivstel_id'=>10], ['prijmeni'=>'asc]);
		 */
		public function options(array $columns, array $options = [], array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
        {
			if(count($options) > 0)
			{
				$where_zal = [];
				foreach($options as $column => $values)
				{
					if((string)$column !== '')
					{
						if((string)$values !== '')
						{
							if(is_array($values) || is_numeric($values))
							{
								unset($options[$column]);
								$where_zal[$column] = $values;
							}
						} else
							unset($options[$column]);
					}
				}

				if(count($where_zal) > 0)
					if(count($where_params) > 0)
						$where_params = ['ORx'=>['ANDx'=>$where_params, 'ORx'=>$where_zal]];
			}

			$params  = [];
			$turn = 1;
			$where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);
			
			$column_id	 = key($columns);
			$column_name = current($columns);

			if($from === null)
				$from = static::TABLE_NAME;
			
			$sql = parent::_options($column_id, $column_name, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC);

			parent::logger($sql, $params);

			self::_infce($column_id);
			
			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			if(is_array($column_name))
			{
				while($zaznam = $stms->fetch())
				{
					$name = '';
					foreach($column_name as $col)
					{
						if($name != '')
							$name .= ' ';

						$name .= $zaznam[$col];
					}

					$options[$zaznam[$column_id]] = $name;
				}
			} else
				while($zaznam = $stms->fetch())
					$options[$zaznam[$column_id]] = $zaznam[$column_name];

			return $options;
		}

		/**
		 * Funkce vrací hashovací pole s klíčem a hodnotami
		 *
		 * @param array $columns
		 * @param array $where_params
		 * @param array $order
		 * @param int $offset
		 * @param int $count
		 * @param ?string $from
		 * @return array
		 *
		 * @example
		 * SELECT uzi_pk_id AS uzivatel_id, uzi_prijmeni AS prijmeni, uzi_jmeno AS jmeno FROM uzivatele WHERE uzi_pk_id > 10 ORDER BY uzi_prijmeni ASC
		 *
		 * $hash = $query->hash(['uzivatel_id'=>['prijmeni','jmeno']], ['>uzivatel_id'=>10], ['prijmeni'=>'asc']);
		 */
		public function hash(array $columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
        {
			$params  = [];
			$turn = 1;
			$where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			$column_id = key($columns);
			$column_values = current($columns);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_hash($column_id, $column_values, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC);

			parent::logger($sql, $params);

			$stms = $this->pdo->prepare($sql);
			$stms->execute($params);

			$result = [];
			while($zaznam = $stms->fetch())
				$result[$zaznam[$column_id]] = $zaznam;

			return $result;
		}

		/**
		 * Funkce vrací pole hodnot daného sloupce $column
		 *
		 * @param string $column
		 * @param array $where_params
		 * @param array $order
		 * @param int $offset
		 * @param int $count
		 * @param ?string $from
		 * @return array
		 *
		 * @example
		 * SELECT uzi_prijmeni AS prijmeni FROM uzivatele WHERE uzi_pk_id > 10 ORDER BY uzi_prijmeni ASC
		 *
		 * $pole = $query->array('prijmeni', ['>uzivatel_id'=>10], ['prijmeni=>'asc']);
		 */
		public function array(string $column, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
        {
			$params  = [];
			$turn = 1;
			$where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

			if($from === null)
				$from = static::TABLE_NAME;

			$sql = parent::_array($column, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

			parent::logger($sql, $params);

			$query = $this->pdo->prepare($sql);
			$query->execute($params);

			$result = [];
			while($zaznam = $query->fetch(PDO::FETCH_NUM))
			{
				if($zaznam[0] != null)
					$result[] = $zaznam[0];
			}

			return $result;
		}

		/**
		 * Funkce vrací pole hodnot daného sloupce $column
		 * Honodty v poli jsou jedinečné
		 *
		 * @param string $column
		 * @param array $where_params
		 * @param array $order
		 * @param int $offset
		 * @param int $count
		 * @param ?string $from
		 * @return array
		 *
		 * @example
		 * SELECT DISTINCT(uzi_prijmeni) AS prijmeni FROM uzivatele WHERE uzi_pk_id > 10 ORDER BY uzi_prijmeni ASC
		 *
		 * $pole = $query->arrayDistinct('prijmeni', ['>uzivatel_id'=>10], ['prijmeni=>'asc']);
		 */
		 public function arrayDistinct(string $column, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
         {
		 	$params  = [];
		 	$turn = 1;
		 	$where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

		 	if($from === null)
		 		$from = static::TABLE_NAME;

		 	$sql = parent::_arrayDistinct($column, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

		 	parent::logger($sql, $params);

		 	$query = $this->pdo->prepare($sql);
		 	$query->execute($params);

		 	$result = [];
		 	while($zaznam = $query->fetch(PDO::FETCH_NUM))
		 	{
		 		if($zaznam[0] != null)
		 			$result[] = $zaznam[0];
		 	}

		 	return $result;
		 }

        /**
         * Funkce vrací maximální číselnou hodnotu sloupčeku $column, pokud není specifikován, bere se PRIMARY KEY
         *
         * @param ?string $column
         * @param array $where_params
         * @param ?string $from
         * @return int
         *
         * @throws ESQLBase
         * @example
         * SELECT IF(MAX(uzi_pk_id)IS NULL, 0, MAX(uzi_pk_id)) FROM uzivatele WHERE uzi_pk_id > 10
         *
         * $max = $query->maxColumn(null, ['>uzivatel_id'=> 10]);
         */
		 public function maxColumn(?string $column = null, array $where_params = [], ?string $from = null) :int
		 {
		 	$params	= [];
		 	$turn = 1;
		 	$where	= parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

		 	if($column === null)
		 		$column = static::TABLE_KEYS[0];

		 	if($from === null)
		 		$from = static::TABLE_NAME;

		 	$sql = 'SELECT IF(MAX('.static::TABLE_COLUMNS[$column]['column'].')IS NULL,0,MAX('.static::TABLE_COLUMNS[$column]['column'].'))'.
			 		parent::_from($from).
			 		parent::_where($where);

		 	parent::logger($sql, $params);

		 	$stms = $this->pdo->prepare($sql);
		 	$stms->execute($params);

		 	if($stms->rowCount() != 1)
		 		throw new  EPDOBase('SQL MAX: Data not found', -300);

		 	return $stms->fetchColumn(0);
		 }

        /**
         * Funkce vrací následující číselnou hodnotu do řady sloupečku $column, pokud není specifikován, bere se PRIMARY KEY
         *
         * @param ?srting $column
         * @param array $where_params
         * @param ?string $from
         * @return int
         *
         * @throws ESQLBase
         * @example
         * SELECT IF(MAX(uzi_pk_id)IS NULL,1, MAX(uzi_pk_id)+1) FROM uzivatele WHERE uzi_pk_id > 10
         *
         * $next = $query->nextColumn(null, ['>uzivatel_id'=> 10]);
         */
		 public function nextColumn(?string $column = null, array $where_params = [], ?string $from = null) :int
		 {
		 	$params	= [];
		 	$turn = 1;
		 	$where	= parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

		 	if($column === null)
		 		$column = static::TABLE_KEYS[0];

		 	if($from === null)
		 		$from = static::TABLE_NAME;

		 	$sql = 'SELECT IF(MAX('.static::TABLE_COLUMNS[$column]['column'].')IS NULL,1,MAX('.static::TABLE_COLUMNS[$column]['column'].')+1)'.
				 	parent::_from($from).
				 	parent::_where($where);

		 	parent::logger($sql, $params);

		 	$stms = $this->pdo->prepare($sql);
		 	$stms->execute($params);

		 	if($stms->rowCount() != 1)
		 		throw new  EPDOBase('SQL MAX: Data not found', -300);

		 	return $stms->fetchColumn(0);
		 }

		 /**
		  * Nastaveni hodnoty AUTO INCREMENT u primárního klíče na dané tabulce
		  *
		  * @param int $auto_increment
		  * @throws  EPDOBase
		  * @return int
		  *
		  * @example
		  * $result = $query->setAutoIncrement(0);
		  */
		 public function setAutoIncrement(int $auto_increment): int
         {
			 $sql = 'ALTER TABLE '.static::TABLE_NAME.' AUTO_INCREMENT='.$auto_increment;

			 parent::logger($sql);

			 $stms = $this->pdo->exec($sql);

			 if($stms !== false)
			 	return $stms;
		 	else
		 		throw new  EPDOBase('AUTO INCREMENT set failed', -240);
		 }

		 /**
		  * Funkce vraci aktualni hodnotu AUTO INCREMENT
		  *
		  * @throws  EPDOBase
		  * @return int
		  *
		  * #example
		  * $currval = $query->currAutoIncrement();
		  */
		 public function currAutoIncrement() :int
		 {
		 	$sql = 'SELECT AUTO_INCREMENT'.
				 	parent::_from('INFORMATION_SCHEMA.TABLES').
				 	parent::_where('TABLE_SCHEMA="'.$this->pdo->getDatabase().'" AND TABLE_NAME="'.static::TABLE_NAME.'"');

		 	parent::logger($sql);

		 	$stms = $this->pdo->query($sql);

		 	if($stms->rowCount() != 1)
		 		throw new  EPDOBase('SQL CurrVal: Data not found', -300);

		 	return intval($stms->fetchColumn(0));
		 }

		 /**
		  * Funkce vrací následující hodnotu AUTO INCREMENT
		  *
		  * @return int
		  *
		  * @example
		  * $nextval = $query->nextAutoIncrement();
		  */
		 public function nextAutoIncrement() :int
		 {
		 	return $this->CurrAutoIncrement() + 1;
		 }

	}

