<?php

class TLangConsoleModule extends TConsoleModule implements IConsoleModule
{
   const string MODULE_NAME = 'lang';
   const array MODULE_ACTIONS = [
   		'csv' 		=> 'creates .csv file with language columns CZ, SK, EN, DE, RU, FR. If is set input file, it will be append',
   		'php'		=> 'creates .php file for specific language from .csv file. You should use -input and -lang parameter for lang specification'
   	];
   
   private array $dirs = [
       __DIR__ . '/../../../src',
       __DIR__ . '/../../../public',
   		__DIR__ .'/../../../cron',
       __DIR__ . '/../../../vendor'
   ];
   
   private array $langs = ['CZ','EN','DE','SK','RU','FR'];

   public function __construct(array $params)
   {
       parent::__construct($params);
       return $this;
   }

    /**
     * @throws EConsoleModule
     */
   public function execute(string &$output) :int
   {
	   	if(isset($this->params['action']))
	   	{
	   		switch($this->params['action'])
	   		{
	   			case 'csv'		:	$lang = TLangParser::getInstance();
									$count = $lang->load($this->dirs, isset($this->params['debug']));

									if($this->params['in'] != '')
									{
										if(!file_exists($this->params['in']))
										{
											$this->params['in'] = self::MODULE_NAME.'/'.$this->params['in'];
											echo PHP_EOL.'Input file changed to "'.$this->params['in'].'"'.PHP_EOL;
										}
										
										echo PHP_EOL.'Loading words from "'.$this->params['in'].'"';
										$count2 = $lang->LoadPhrases($this->params['in']);
										echo PHP_EOL.'Total append words: '.$count2;
									}
					   			  
									if($this->params['out'] != '')
									{
										echo PHP_EOL.'Append words to another CSV "'.$this->params['out'].'"';
										$output = $lang->AppendToCSV($this->params['out']);	
					   			  	
										if($this->params['file'] == '')
											$this->params['file'] = $this->params['out'];
									} else {
										$output = $lang->SaveToCSV();
					   			  
										if($this->params['file'] == '')
											$this->params['file'] = 'lang.csv';
									}
					   			  
									echo PHP_EOL.'Total saved items: '.$count.PHP_EOL;
	   				break;

	   			case 'php'		:	if($this->params['in'] != '')
									{
										if($this->params['lang'] != '')
										{
											$lang = TLangParser::getInstance();
											$this->params['lang'] = strtoupper($this->params['lang']);
			   							  
											if(!file_exists($this->params['in']))
												if(file_exists(self::MODULE_NAME.'/'.$this->params['in']))
												{
													$this->params['in'] = self::MODULE_NAME.'/'.$this->params['in'];
													echo PHP_EOL.'Input file changed to "'.$this->params['in'].'"'.PHP_EOL;
												}

											$lang->LoadFromCSV($this->params['in'], array_search($this->params['lang'], $this->langs) + 1);		   							  
											$output = $lang->SaveToPHP($this->params['lang']);
			   							  
											if($this->params['file'] == '')
			   							  		$this->params['file'] = 'Lang'.$this->params['lang'].'.php';
											
		   							  	} else
		   							  		throw new EConsoleModule('Action "'.$this->params['action'].'" needs -lang parameter, see help!', self::ERR_PARAM_NOT_FOUND-1);
	   								  } else
	   								  	  throw new EConsoleModule('Action "'.$this->params['action'].'" needs -in parameter, see help!', self::ERR_PARAM_NOT_FOUND);
	   				break;
	   				
	   			case 'help'		:	$output = $this->help();
	   				break;
	   				
	   			default			:	throw new EConsoleModule('Action "'.$this->params['action'].'" not defined, see help!', self::ERR_ACTION_NOT_FOUND);
	   				break;
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
	   			$output = 'File "'.$this->params['file'].'" successfully saved (size '.number_format((filesize($this->params['file']) / 1000), 3, '.', ' ').' kB).';
	   			
	   			return self::SUCCESS_CODE_PRINT;
	   		}
	   		
	   	} else
	   		$output = $this->help();
   
	   	return self::SUCCESS_CODE_OK;	
   }
   
   public function help() :string
   {   
   		$actions = '';
   		foreach(self::MODULE_ACTIONS as $name => $desc)
   			$actions .= PHP_TAB . str_pad($name, 7, ' ').' - '.$desc.PHP_EOL;
   		
   		return 	'actions:'.PHP_EOL.
   				$actions.PHP_EOL.
   				'[-debug] '.PHP_TAB.PHP_TAB.PHP_TAB.'- saves all items with duplicity, and name of method in the .csv file;'.PHP_EOL.
		   		'[-in <filename>] '.PHP_TAB.PHP_TAB.'- load words from specific file in .csv format to generate .php or append to new .csv file;'.PHP_EOL.
		   		'[-out <filename>] '.PHP_TAB.PHP_TAB.'- add words into specific file in .csv or .php format, depends on called method if exists;'.PHP_EOL.
		   		'[-lang <'.implode('|', $this->langs).'>] '.PHP_TAB.'- language to found mutation in the specific column in .csv file and generate it to .php file'.PHP_EOL;
   }   
}