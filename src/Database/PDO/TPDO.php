<?php

  /**
   * Třída pro práci s databází přes PDO
   *
   * @name TPDO
   * @author vladimir.horky
   * @copyright Vladimír Horký, 2025
   * @version 3.0
   *
   */
	class TPDO extends PDO
	{
		public const int PDO_DRIVER_MYSQL 	= 1;
        public const int PDO_DRIVER_DBLIB	= 2;
        public const int PDO_DRIVER_SQLSRV	= 3;
        public const int PDO_DRIVER_SQLITE	= 4;
        public const int PDO_DRIVER_PGSQL	= 5;
        public const int PDO_DRIVER_OCI     = 6;

        private ?string $database;

		public function __construct(array $config, string $option = '')
		{
			$options = [
				PDO::ATTR_TIMEOUT 			 => 3, // timeout in seconds
				PDO::ATTR_ERRMODE 			 => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
			];

            $commands = [];

			switch($config['driver'])
			{
				case self::PDO_DRIVER_DBLIB		:	$dsn = 'dblib:host='.$config['host'].';dbname='.$config['database'];
													break;

				case self::PDO_DRIVER_SQLSRV	: 	$dsn = 'sqlsrv:server='.$config['host'].';Database='.$config['database'].';TrustServerCertificate=yes';
													break;

				case self::PDO_DRIVER_SQLITE	:	$dsn = 'sqlite:'.$option;
													break;

				case self::PDO_DRIVER_PGSQL		:	$dsn = 'pgsql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'];
													break;

                case self::PDO_DRIVER_OCI       :   $dsn = 'oci:dbname=//'.$config['host'].':'.$config['port'].'/'.$config['database'].';charset='.$config['charset']; //.','IFSAPP', 'IAtgaw2023''
                                                    $commands[] = 'ALTER SESSION SET NLS_DATE_FORMAT=\'YYYY-MM-DD hh24:mi:ss\'';
                                                    $commands[] = 'ALTER SESSION SET NLS_LANGUAGE=\'CZECH\'';
                                                    break;

                case self::PDO_DRIVER_MYSQL		:
				default							:	$dsn = 'mysql:host='.$config['host'].';dbname='.$config['database'];
													//$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES cp1250';

                                                   // $command = 'SET NAMES UTF8';
                                                    $command = 'SET NAMES \'UTF8\' COLLATE \'utf8_unicode_ci\';';
                                                    if(isset($config['wait_timeout']))
                                                    {
                                                        $timeout = $config['wait_timeout'];
                                                        $command .= ', WAIT_TIMEOUT = '.$timeout.', SESSION WAIT_TIMEOUT = '.$timeout.', SESSION INTERACTIVE_TIMEOUT = '.$timeout;
                                                    }

                                                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = $command;
                                                    $options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
                                                    $options[PDO::ATTR_PERSISTENT] = true;
                                                    break;
                                                }

            if(isset($config['timeout']))
            {
                if($config['driver'] !== self::PDO_DRIVER_SQLSRV)
                    $options[PDO::ATTR_TIMEOUT] = $config['timeout'];
                else
                    $options[PDO::SQLSRV_ATTR_QUERY_TIMEOUT] = $config['timeout'];
            }

			$this->database = $config['database'];
            //$password = self::pdecrypt($config['password']);
            $password = $config['password'];

			parent::__construct($dsn, $config['login'], $password, $options);
         
           foreach($commands as $command)
            {
                parent::exec($command);
            }
		}

        public function setTimeOut(int $seconds): void
        {
            $this->query('SET wait_timeout = '.$seconds);
            $this->query('SET session wait_timeout = '.$seconds);
        }

		public function getDatabase(): ?string
		{
			return $this->database;
		}

		public function is_null(?string $value, string $str): string
		{
			if($value == '' || $value == 'null')
				return 'null';
			else
				return $str;
		}

		public function make_array_vars($var, array $values) :array
		{
			$result = [];

			$i = 0;
			foreach($values as $value)
				$result[$var.$i] = $values[$i++];

			return $result;
		}

		public function select_db(string $database) :int
		{
			$this->database = $database;
			return $this->exec('use '.$database);
		}

		public function query_to_array($query, array $options = [], array $params = []): array
        {
			$query->execute($params);
			while($zaznam = $query->fetch(PDO::FETCH_NUM))
				$options[$zaznam[0]] = $zaznam[1];

			return $options;
		}

		public static function reverseOrder(string $value) :string
		{
			if($value === 'asc')
				return 'desc';
			else
				return 'asc';
		}

		/********************************************************************************
		 * formatovani hodnot
		 *******************************************************************************/

		public static function genNewPassword() :string
		{
			srand();
			$chars = 'ABDEFHTP3456789apejdfrmnbh';
			$result = '';

			for($i = 0; $i <= 6; $i++)
				$result .= $chars[rand(0,strlen($chars)-1)];

				return $result;
		}

		/********************************************************************************
		 * debugovani
		 *******************************************************************************/

		public static function debug(string $sql) :bool
		{
			$keys = ['VALUES', ' SET ', ' FROM', ' WHERE', ' ORDER BY', ' GROUP BY ', ' HAVING ', ' LIMIT ', ' UNION ALL ', ' UNION ', ' SELECT '];
			foreach($keys as $key)
				$sql = str_replace($key, "\n".ltrim($key), $sql);

			echo '<pre>'.$sql.'</pre>';

			return true;
		}

		public function log(string $sql) :bool
		{
			$keys = ['VALUES', ' SET ', ' FROM', ' WHERE', ' ORDER BY', ' GROUP BY ', ' HAVING ', ' LIMIT ', ' UNION ALL ', ' UNION ', ' SELECT '];
			foreach($keys as $key)
				$sql = str_replace($key, "\n".ltrim($key), $sql);

            $filename = TConfig::DIR_LOG.'sql.log';
			if(file_exists($filename))
				$data = file_get_contents($filename);

			$data .= $sql . "\r\n\r\n";
			file_put_contents($filename, $data);

			return true;
		}
		
		public function Export(?string $filename): bool|string
        {
			$export = new TSQLParser($this);
			return $export->Export($filename);
		}
		
		public function ExportCmdToFile($file, $login, $password, $database, $host = null) :int
		{
            if(str_contains(' ', TConfig::EXE_MYSQL_DUMP) && stripos('.exe', TConfig::EXE_MYSQL_DUMP) !== false)
				$command = '"'.TConfig::EXE_MYSQL_DUMP.'" --user='.$login.' --password='.$password;
			else
				$command = TConfig::EXE_MYSQL_DUMP.' --user='.$login.' --password='.$password;
					
			if($host != '')
				$command .= ' --host='.$host;
						
			$command .= ' '.$database; //.' 2>&1 > "'.$file.'"';
						
			$output = [];
			$code = '';
			$cmd = exec($command, $output, $code);
				
			file_put_contents($file, implode("\n", $output));
						
			//if(count($output) == 0)
			return 1;
			//else
			//throw new Exception($output[0]);
		}

        /**
         * @throws Exception
         */
        public function ExportCmdToStr($login, $password, $database, $host = null) :string
		{
            if(str_contains(' ', TConfig::EXE_MYSQL_DUMP) && stripos('.exe', TConfig::EXE_MYSQL_DUMP) !== false)
				$command = '"'.TConfig::EXE_MYSQL_DUMP.'" --user='.$login.' --password='.$password;
			else
				$command = TConfig::EXE_MYSQL_DUMP.' --user='.$login.' --password='.$password;
					
			if($host != '')
				$command .= ' --host='.$host;
						
			$command .= ' '.$database;
						
			$output = [];
			$code = '';
			$cmd = exec($command, $output, $code);

			if(count($output) > 1)
				return implode("\n", $output);
			else
				throw new Exception($output[0]);
		}
		
		public function Import($content, &$inserts, &$others): int
        {
			$import = new TSQLParser($this);
			return $import->Import($content, $inserts, $others);
		}

        /**
         * @throws Exception
         */
        public function ImportFromFile($filename, &$inserts, &$others): int
        {
			if(file_exists($filename))
			{
				$content = file_get_contents($filename);
				if($content != '')
					return $this->Import($content, $inserts, $others);
				else
					throw new Exception ('File \''.$filename.'\' is empty!');
			} else
				throw new Exception('File \''.$filename.'\' does not exists!');
		}

        /**
         * @throws Exception
         */
        public function ImportCmdFromFile($file, $login, $password, $database, $host = null): int
        {
			$command = '"'.TConfig::EXE_MYSQL.'" --user='.$login.' --password='.$password;
			
			if($host != '')
				$command .= ' --host='.$host;
				
			$command .= ' '.$database.' 2>&1 < "'.$file.'"';
				
			$output = [];
			$code = '';
			$cmd = exec($command, $output, $code);
				
			if(count($output) == 0)
				return 1;
			else
				throw new Exception($output[0]);
		}

        /**
         * @throws Exception
         */
        public function ImportCmdFromStr($sql, $login, $password, $database, $host = null): int
        {
			$file = sys_get_temp_dir().'/temp.sql';
			file_put_contents($file, $sql);
			
			try {
				$output = self::ImportCmdFromFile($file, $login, $password, $database, $host);
            } finally {
				@unlink($file);
			}
			
			return $output;
		}
	}