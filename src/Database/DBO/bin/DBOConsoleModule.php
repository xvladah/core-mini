<?php

/**
 * Třída pro generovani SQL skriptů
 *
 * @name		TDBOConsoleModule
 * @version 	0.5
 * @author		vladimir.horky
 * @copyright	Vladimir Horky, 2019 
 */

const MYSQL_ENGINE = 'InnoDB';
const MYSQL_COLLATE = 'utf8_general_ci';
const REPOSITORY_ROOT = __DIR__ . '/../../../src/Repository/';

class TDBOConsoleModule extends TConsoleModule implements IConsoleModule
{
	const string MODULE_NAME 	 = 'dbo';
	const array MODULE_ACTIONS = [
		'create' => 'generates SQL script with CREATE TABLE statements (-file <file>)',
		'alter'  => 'generates SQL script with ALTER TABLE statements (-file <file>)',
		'delete' => 'generates SQL script with DROP TABLE statements (-file <file>)',
		'list'	 => 'print all classes to generate SQL script (-file <file>)',
		'update' => 'generates SQL updates for database by dbo classes structure, not implemented yet (-file <file>)',
		'reverse'=> 'generates dbo classes structure from database, not implemented yet (-file <file>)'
	];	

	private array $classes = [];
	private array $files   = [];
	private string $search;
	
	public function __construct(array $params)	
	{
		parent::__construct($params);
		$this->search = REPOSITORY_ROOT;
	}
		
	public function setSearch(string $search)
	{
		$this->search = $search;
		return $this;
	}
		
	private function searchForFiles(string $path)
	{
		$dirs = scandir($path);
		foreach($dirs as $dir)
		{
			$actdir = $path.$dir.'/';
			if(is_dir($actdir) && !in_array($dir, ['.','..']))
			{
				$files = scandir($actdir);
				foreach($files as $file)
				{
					if(is_file($actdir.$file) && !in_array($file, ['.','..']))
						$this->files[] = $actdir.$file;
				}
			}
		}

		return count($this->files);
	}

	public function create(array $classNames) :string
	{			
		$output = '';
		foreach($classNames as $className)
		{
			$output .= 	'--'.PHP_EOL.
						'-- Table structure for class '.$className.PHP_EOL.
						'--'.PHP_EOL.PHP_EOL;
			
			$i = 1;
			if(isset($this->params['add-drop-table']))
				$output .= 'DROP TABLE `'.$className::TABLE_NAME.'`;'.PHP_EOL;
				
			$output .= 'CREATE TABLE IF NOT EXISTS `'.$className::TABLE_NAME.'` ('.PHP_EOL;
			foreach($className::TABLE_COLUMNS as $column_id => $column)
			{
				if($column['use'] == '' || $column['use'] == TSQLBase::COLUMN_BASE)
				{
					if($i++ > 1)
						$output .= ','.PHP_EOL;
							
					if(in_array($column_id, $className::TABLE_KEYS))
						$AUTOINCREMENT = ' AUTO_INCREMENT';
					else
						$AUTOINCREMENT = '';
						
					switch($column['type']) {
						case $className::DATA_TYPE_STR				:	if($column['max_length'] != '')
																			$type = 'VARCHAR('.$column['max_length'].')';
																		else
																			$type = 'VARCHAR(255)';
							break;

						case $className::DATA_TYPE_CURRENCY			:	$type = 'DECIMAL(10,2)'; break;
						case $className::DATA_TYPE_FLOAT			:	$type = 'FLOAT'; break;
						case $className::DATA_TYPE_DOUBLE			:	$type = 'DOUBLE'; break;
						
						case $className::DATA_TYPE_TEXT				:	$type = 'TEXT'; break;
						case $className::DATA_TYPE_JSON				:	$type = 'JSON'; break;
						
						case $className::DATA_TYPE_UINT				:	$type = 'INT UNSIGNED'; break;
						case $className::DATA_TYPE_UBIGINT			:	$type = 'BIGINT UNSIGNED'; break;
						case $className::DATA_TYPE_USMALLINT		:	$type = 'SMALLINT UNSIGNED'; break;
						case $className::DATA_TYPE_UTINYINT			:	$type = 'TINYINT UNSIGNED'; break;
						
						case $className::DATA_TYPE_INT				:	$type = 'INT'; break;
						case $className::DATA_TYPE_BIGINT			:	$type = 'BIGINT'; break;
						case $className::DATA_TYPE_SMALLINT			:	$type = 'SMALLINT'; break;
						case $className::DATA_TYPE_TINYINT			:	$type = 'TINYINT'; break;
						
						case $className::DATA_TYPE_DATETIME_UNIX	:	$type = 'INT'; break;
						
						case $className::DATA_TYPE_DATETIME			:	$type = 'DATETIME'; break;
						case $className::DATA_TYPE_DATE				:	$type = 'DATE'; break;
						case $className::DATA_TYPE_TIME				:	$type = 'TIME'; break;
						default										:	$type = '?'; break;
					}
					
					if($column['null'] == $className::NULL_YES)
						$null = ' NULL';
					else
						$null = ' NOT NULL';
							
					if($column['default'] != '')
						$default = ' DEFAULT `'.$column['default'].'`';
					else
						$default = '';
									
					$output .= PHP_TAB.'`'.$column['column'].'` '.$type.$null.$AUTOINCREMENT.$default;
				}
			}
				
			foreach($className::TABLE_KEYS as $key)
			{
				if($i++ > 1)
					$output .= ','.PHP_EOL;
						
				$output .= PHP_TAB.'PRIMARY KEY (`'.$className::TABLE_COLUMNS[$key]['column'].'`)';
			}
				
			$output .= PHP_EOL.') COLLATE=`'.MYSQL_COLLATE.'` ENGINE='.MYSQL_ENGINE.' AUTO_INCREMENT=1;'.PHP_EOL.PHP_EOL;
		}

		return $output;
	}
	
	public function update(array $classNames) :string
	{
		$output = 	'--'.PHP_EOL.
					'-- Update tables for changes'.PHP_EOL.
					'--'.PHP_EOL.PHP_EOL;
		
		foreach($classNames as $className)
		{
			$output .= 'ALTER TABLE `'.$className::TABLE_NAME.'`;'.PHP_EOL;
			
		}
		
		return $output;
	}
	
	public function delete(array $classNames) :string
	{
		$output = 	'--'.PHP_EOL.
					'-- Delete tables'.PHP_EOL.
					'--'.PHP_EOL.PHP_EOL;
		
		foreach($classNames as $className)
		{	
			$output .= 'DROP TABLE `'.$className::TABLE_NAME.'`;'.PHP_EOL;
		}
				
		return $output;		
	}

    /**
     * @throws EConsoleModule
     */
    public function execute(string &$output) :int
	{
		if(isset($this->params['action']))
		{
			$this->searchForFiles($this->search);

			foreach($this->files as $file)	
			{
				$content = file_get_contents($file);
				$matches = [];
				if(preg_match('/class[\s]+([A-z0-9_]+)[\s]+extends[\s]+(TPDOBase|TSQLiteBase|TSQLBase|TMSSQLBase|TMySQLBase)/i', $content, $matches))
              					if(preg_match('/const[\s]TABLE_NAME/i', $content))
					$this->classes[$file] = $matches[1];
			}

			$classNames = [];
			if(is_string($this->params['class']))
			{
				$items = explode(',',$this->params['class']);
				foreach($items as $item)
				{
					if(strpos($item, '*') !== false)
					{
						$mask = str_replace('*', '(.*)', $item);
						foreach($this->classes as $file => $className)
						{
							if(!in_array($className, $classNames))
								if(preg_match('/^'.$mask.'$/i', $className))
									$classNames[$file] = $className;
						}
					} else {
						$key = array_search($item, $this->classes);
						if($key !== false)
							$classNames[$key] = $item;
						else
							throw new EConsoleModule('Input class "'.$item.'" definition not found!', self::ERR_CLASS_NOT_FOUND);
					}
				}
			} else {
				foreach($this->classes as $file => $className)
					$classNames[$file] = $className;
			}
				
			ksort($classNames);
			
			foreach($classNames as $file => $className)
				require_once $file;
			
			switch($this->params['action'])
			{
				case 'create' 	: $output = $this->create($classNames); break;
				case 'alter'	: $output = $this->update($classNames); break;
				case 'delete'	: $output = $this->delete($classNames); break;
				case 'list'		: $output = $this->list($classNames); break;
				case 'update'	: $output = 'Function not implemented yet'; break;
				case 'reverse'	: $output = 'Function not implemented yet'; break;
				case 'help'		: $output = $this->help(); break;
				default			: throw new EConsoleModule('Action "'.$this->params['action'].'" not defined, see help!', self::ERR_ACTION_NOT_FOUND);
			}
			
			if(isset($this->params['file']))
			{
				if(basename($this->params['file']) == $this->params['file'])
				{
					if(!file_exists(self::MODULE_NAME))
					{
						if(mkdir(self::MODULE_NAME, 0777))
							$allow = true;
					} else
						if(is_dir(self::MODULE_NAME))
							$allow = true;
						
					if($allow)		
						$this->params['file'] = self::getNextFileName(self::MODULE_NAME.'/'.$this->params['file']);
				}

				file_put_contents($this->params['file'], $output);
				$output = 'File "'.$this->params['file'].'" successfully created (size '.number_format((filesize($this->params['file']) / 1000), 3, '.', ' ').' kB).';
				
				return self::SUCCESS_CODE_PRINT;
			}
		} else
			$output = $this->help();
	
		return self::SUCCESS_CODE_OK;	
	}
	
	public function list(array $classNames) :string
	{
		$output = '';
		
		$max = 29;
		foreach($classNames as $className)
			if(strlen($className) > $max)
				$max = strlen($className);

		$max++;		
				
		foreach($classNames as $className)
			$output .= '   '.str_pad($className,$max,' ').'['.$className::TABLE_NAME.']'.PHP_EOL;
		
		return $output;
		
	}
	
	public function help() :string
	{
		$actions = '';
		foreach(self::MODULE_ACTIONS as $name => $desc)
			$actions .= PHP_TAB . str_pad($name, 7, ' ').' - '.$desc.PHP_EOL;
		
		return 	'actions:'.PHP_EOL.
				$actions.PHP_EOL.
				'[-file <filename>] '.PHP_TAB.PHP_TAB.'- saves generated SQL script to the given file'.PHP_EOL.
				'[-add-drop-table] '.PHP_TAB.PHP_TAB.'- inserts DROP TABLE before each CREATE TABLE definition'.PHP_EOL.
				'[-class <names|expression>] '.PHP_TAB.'- classes list (separated by ",") to generate SQL script;'.PHP_EOL.
											PHP_TAB.PHP_TAB.PHP_TAB.PHP_TAB.'  you can use expression with "*" to specify class group (etc. TEBASE*Pra*).'.PHP_EOL.
											PHP_TAB.PHP_TAB.PHP_TAB.PHP_TAB.'  If not specified, generate all';
	}
}