<?php

class TSQLParser
{	
	public const string CRLF = "\n";
	private TPDO $pdo;
	private bool $short_insert = true;
	private int $short_insert_count = 30;
	
	private string $encoding_input = 'windows-1250';
	private string $encoding_output = 'windows-1250';

	public function __construct(TPDO $pdo)
	{
		$this->pdo = $pdo;
	}
	
	public function setEncodings(?string $input, string $output): static
    {
		$this->encoding_input = $input;
		$this->encoding_output = $output;
		return $this;
	}
	
	protected function convertStr(?string $value)
	{
		if($this->encoding_input !== $this->encoding_output)
			return iconv($this->encoding_input, $this->encoding_output, $value);
		else
			return $value;
	}
	
	public function setShortInsertCount($short_insert_count): static
    {
		$this->short_insert_count = $short_insert_count;
		return $this;
	}
	
	public function setShortInsert($short_insert): static
    {
		$this->short_insert = $short_insert;
		return $this;
	}
	
	public static function Tr($str): string
    {
		return StrTr($str, "áäąčćďéěëęíňóöřŕšśťúůüýžźżńľłĺÁÄĄČĆĎÉĚËĘÍŇŃÓÖŘŔŠŚŤÚŮÜÝŽŹŻĽĹŁ", "aaaccdeeeeinoorrsstuuuyzzznlllAAACCDEEEEINNOORRSSTUUUYZZZLLL");
	}
	
	public static function TrArray(array $str): array
    {
		$result = [];
		foreach($str as $key => $value)
			$result[$key] = self::Tr($value);
			
		return $result;
	}
	
	public static function StrToLower(?string $str): string
    {
		$result = strtolower($str);
		return StrTr($result, 'ÁÄĄČĆĎÉĚËĘÍŇŃÓÖŘŔŠŚŤÚŮÜÝŽŹŻĽĹŁ', 'áäąčćďéěëęíňńóöřŕšśťúůüýžźżľĺł');
	}
	
	public static function StrToUpper(?string $str): string
    {
		$result = strtoupper($str);
		return StrTr($result, 'áäąčćďéěëęíňńóöřŕšśťúůüýžźżľĺł', 'ÁÄĄČĆĎÉĚËĘÍŇŃÓÖŘŔŠŚŤÚŮÜÝŽŹŻĽĹŁ');
	}
	
	public function StrLower(?string $value, string $defaults = '')
	{
		return $this->Str(self::StrToLower($value), self::StrToLower($defaults));
	}
	
	public function StrLowerNull(?string $value)
	{
		return $this->StrNull(self::StrToLower($value));
	}
	
	public function StrUpper(?string $value, string $defaults = '')
	{
		return $this->Str(self::StrToUpper($value), self::StrToUpper($defaults));
	}
	
	public function StrUpperNull(?string $value): ?string
    {
		return $this->StrNull(self::StrToUpper($value));
	}	
	
	public function ImplodeNumber(array $values, int $default = -1): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			$result .= $this->Number($value, $default);
		}

		return '('.$result.')';
	}
	
	public function ImplodeNumberNull(array $values): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			if($value != 'null' && $value != '')
				$result .= $this->NumberNull($value);
		}

		return '('.$result.')';
	}
	
	public function NumberList(?string $value): ?string
	{
		$pole = explode(',',$value);
		if(count($pole) > 0)
		{
			$result = '';
			foreach($pole as $item)
			{
				if($result != '')
					$result .= ',';
				
				$result .= $this->Number($item);
			}
			
			return $result;
		} else
			return $value;
			
	}
	
	public function ImplodeStr(array $values, string $default = ''): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			$result .= $this->Str($value, $default);
		}
		
		return '('.$result.')';
	}
	
	public function ImplodeLowerStr(array $values, string $default = ''): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';
			
			$result .= $this->StrLower($value, $default);
		}
		
		return '('.$result.')';
	}
	
	public function ImplodeStrNull(array $values): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			$result .= $this->StrNull($value);
		}
		
		return '('.$result.')';
	}
	
	public function ImplodeLowerStrNull(array $values, string $default = ''): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($value != '')
			{
				if($result != '')
					$result .= ',';

				$result .= $this->StrLowerNull($value);
			}
		}
		
		return '('.$result.')';
	}
	
	public function StrList(?string $value): ?string
    {
		$pole = explode(',',$value);
		if(count($pole) > 0)
		{
			$result = '';
			foreach($pole as $item)
			{
				if($result != '')
					$result .= ',';
				
				$result .= $this->Str($item);
			}
			
			return $result;
		} else
			return $value;
	}

	// Date
	public function ImplodeDate(array $values, int $default = -1): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			$result .= $this->Date($value, $default);
		}

		return '('.$result.')';
	}
	
	public function ImplodeDateNull(array $values): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			if($value != 'null' && $value != '')
				$result .= $this->DateNull($value);
		}
		
		return '('.$result.')';
	}
	
	public function DateList(?string $value): ?string
    {
		$pole = explode(',',$value);
		if(count($pole) > 0)
		{
			$result = '';
			foreach($pole as $item)
			{
				if($result != '')
					$result .= ',';
				
				$result .= $this->Date($item);
			}
			
			return $result;
		} else
			return $value;
	}
	
	// Time
	public function ImplodeTime(array $values, int $default = -1): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			$result .= $this->Time($value, $default);
		}
		
		return '('.$result.')';
	}

	public function ImplodeTimeNull(array $values): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			if($value != 'null' && $value != '')
				$result .= $this->TimeNull($value);
		}

		return '('.$result.')';
	}
	
	public function TimeList(?string $value): ?string
    {
		$pole = explode(',',$value);
		if(count($pole) > 0)
		{
			$result = '';
			foreach($pole as $item)
			{
				if($result != '')
					$result .= ',';
				
				$result .= $this->Time($item);
			}
			
			return $result;
		} else
			return $value;
	}
	
	// DateTime
	public function ImplodeDateTime(array $values, int $default = -1): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			$result .= $this->DateTime($value, $default);
		}
		
		return '('.$result.')';
	}
	
	public function ImplodeDateTimeNull(array $values): string
    {
		$result = '';
		foreach($values as $value)
		{
			if($result != '')
				$result .= ',';

			if($value != 'null' && $value != '')
				$result .= $this->DateTimeNull($value);
		}
		
		return '('.$result.')';
	}
	
	public function DateTimeList(?string $value): ?string
    {
		$pole = explode(',',$value);
		if(count($pole) > 0)
		{
			$result = '';
			foreach($pole as $item)
			{
				if($result != '')
					$result .= ',';

				$result .= $this->DateTime($item);
			}

			return $result;
		} else
			return $value;
	}

	protected static function getTableKey(?string $value, string|int $def): string
    {
		if($value == 'PRIMARY')
			return 'PRIMARY KEY';
		else
			if($def == '0')
				return 'UNIQUE KEY '.$value;
			else
				return 'KEY '.$value.' ';
	}
	
	protected static function getTableNull(?string $value): string
    {
		if($value != 'YES')
			return ' NOT NULL';
		else
			return '';
	}
	
	protected static function getTableExtra(?string $value): string
    {
		if($value != '')
			return ' '.$value;
		else
			return '';
	}
	
	protected static function getTableType(?string $value): string
    {
		if($value != '')
			return ' '.$value;
		else
			return '';
	}
	
	protected static function getTableDefault(?string $value): ?string
    {
		if($value != '')
			return ' DEFAULT "'.addSlashes($value).'"';
		else
			return '';
	}

	protected static function getValue(?string $typ, ?string $value, ?string $null): ?string
    {
		if(str_contains($typ, 'char') || str_starts_with($typ, 'char') || in_array($typ, ['text','time','date','datetime','blob']))
		{
			if($value == '')
			{
				if($null == 'yes')
					return 'NULL';
				else
					return '""';
			} else
				return '"'.addSlashes($value).'"';
		} else {
			if($value == '')
			{
				if($null == 'yes')
					return 'NULL';

				return '0';
			} else
				return $value;
		}
	}
	
	protected function getTableData(?string $table, bool $lock_tables = false): string
    {
		$sloupce = [];
		
		$dump = '';
		$i = 0;
		
		$query = $this->pdo->query('SHOW COLUMNS FROM '.$table);
		while($zaznam = $query->fetch(PDO::FETCH_NUM))
		{
			foreach($zaznam as $item)
				$sloupce[$i][] = strtolower($item);
				
			$i++;
		}
		
		$c = 0;
		$insert = '';
		$query = $this->pdo->query('SELECT * FROM '.$table);
		while($data = $query->fetch(PDO::FETCH_NUM))
		{	
			$values = '';
			
			$i = 0;
			foreach($sloupce as $sloupec)
			{
				if($i > 0)
					$values .= ',';
				
				$values .= $this->convertStr(self::getValue($sloupec[1], $data[$i], $sloupec[2]));
				$i++;
			}
			
			if($this->short_insert)
			{				
				if($c >= $this->short_insert_count)
				{
					$insert .= ';'.self::CRLF.'INSERT INTO '.$table.' VALUES('.$values.')';
					$c = 0;
				} else {
					if($insert != '')
						$insert .= ',';
					
					$insert .= '('.$values.')';
				}

				$c++;
			} else	
				$insert .= 'INSERT INTO '.$table.' VALUES('.$values.');'.self::CRLF;
		}

		if($insert != '')
		{
			if($this->short_insert)
				$dump .= 'INSERT INTO '.$table.' VALUES'.$insert.';'.self::CRLF;
			else	
				$dump .= $insert.self::CRLF;
		}
		
		if($dump != '')
		{
			if($lock_tables)
				return 'LOCK TABLES '.$table.' WRITE;'.self::CRLF.'ALTER TABLE '.$table.' DISABLE KEYS;'.self::CRLF.$dump.'ALTER TABLE '.$table.' ENABLE KEYS;'.self::CRLF.'UNLOCK TABLES;'.self::CRLF;
			else
				return $dump.self::CRLF;
		} else
			return '';
	}
	
	protected function getTableDefinition(?string $table, ?string $engine = null, ?string $collation = null): string
    {
        $dump = '';

		$dump .= 'DROP TABLE IF EXISTS '.$table.';'.self::CRLF;
		$dump .= 'CREATE TABLE '.$table.' ('.self::CRLF;
		
		$query = $this->pdo->query('SHOW COLUMNS FROM '.$table);
		$i = 0;
		while($zaznam = $query->fetch(PDO::FETCH_NUM))
		{
			if($i > 0)
				$dump .= ','.self::CRLF;
				
			$dump .= $zaznam[0].self::getTableType($zaznam[1]).self::getTableDefault($zaznam[4]).self::getTableNull($zaznam[2]).self::getTableExtra($zaznam[5]);
			$i++;
		}
		
		$zal = '';
		$pri = false;
		$query = $this->pdo->query('SHOW KEYS FROM '.$table);
		while($zaznam = $query->fetch(PDO::FETCH_NUM))
		{
			if($zaznam[2] == 'PRIMARY')
			{
				if($zal != $zaznam[2])
				{
					$dump .= ','.self::CRLF.self::getTableKey($zaznam[2],$zaznam[1]).' ('.$zaznam[4];
					$zal = $zaznam[2];
				} else
					$dump .= ','.$zaznam[4];
			} else {
				if($zal != $zaznam[2])
				{
					if($pri)
						$dump .= ')';
						
					$dump .= ','.self::CRLF.self::getTableKey($zaznam[2],$zaznam[1]).'('.$zaznam[4];
					if($zaznam[7] != '')
						$dump .= '('.$zaznam[7].')';
							
					$zal = $zaznam[2];
				} else
					$dump .= ','.$zaznam[4];
			}
			
			$i++;
			$pri = true;
		}

		if($pri)
			$dump .= ')';
		
		$table_engine = '';	
		if($engine !== null)
		{
			$table_engine = 'ENGINE='.$engine;
			if($collation !== null)
				$table_engine .= ' DEFAULT COLLATE='.$collation;
		}

		return $dump.self::CRLF.')'.$table_engine.';'.self::CRLF.self::CRLF;
	}

    public function actualCodePage() :?string
    {
        $query = $this->pdo->query('SHOW VARIABLES LIKE \'character_set_client\'');
        return $query->fetch()['Value'];
    }

	public function Export(?string $filename = null, array $ignored = [], ?string $destCodePage = null, bool $lock_tables = false, bool $in_transaction = false, bool $directives = false): true|string
    {
        if($destCodePage !== null)
        {
            $srcCodePage = $this->actualCodePage();
            if(strcasecmp($srcCodePage, $destCodePage) == 0)
                $this->pdo->query('SET NAMES \''.$destCodePage.'\' COLLATE \''.$destCodePage.'_general_ci\';');
            else
                $srcCodePage = null;
        } else
            $srcCodePage = null;

		$tabulky = [];

		$query = $this->pdo->query('SHOW TABLE STATUS');
		while($zaznam = $query->fetch())
		{
			$tabulky[$zaznam['Name']] = [
				'engine'		=> $zaznam['Engine'],	
				'collation' 	=> $zaznam['Collation'],
				'format'		=> $zaznam['Row_format'],
				'auto_increment'=> $zaznam['Auto_increment']
			];
		}
        
		$dump = '';
		foreach($tabulky as $nazev => $tabulka)
		{
			$dump .= $this->getTableDefinition($nazev, $tabulka['engine'], $tabulka['collation']);

			if(!in_array($nazev, $ignored))
				$dump .= $this->getTableData($nazev, $lock_tables).self::CRLF;
		}

		if($dump != '')
		{
			if($in_transaction)
				$dump = 'START TRANSACTION;'.self::CRLF.self::CRLF.$dump.'COMMIT;'.self::CRLF;

			if($directives)
			{
				$dump = '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE=\'+00:00\' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'NO_AUTO_VALUE_ON_ZERO\' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;'.self::CRLF.self::CRLF.$dump.self::CRLF.'/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;';
			}
		}

        if($srcCodePage !== null)
            $this->pdo->query('SET NAMES \''.$srcCodePage.'\' COLLATE \''.$srcCodePage.'_general_ci\';');

		if($filename !== null)
		{
			file_put_contents($filename, $dump);
			return true;
		} else
			return $dump;
	}

	public function ExportTablesarray ($tabulky = []) :string
	{
		$dump = '';
		for($i = 0; $i < count($tabulky); $i++)
		{
			$dump .= $this->getTableDefinition($tabulky[$i]);
			$dump .= $this->getTableData($tabulky[$i]).self::CRLF;
		}

		return $dump;
	}

	public function Import($content, &$inserts, &$others, $destCodePage = null): int
    {
        if($destCodePage !== null)
        {
            $srcCodePage = $this->actualCodePage();
            if(strcasecmp($srcCodePage, $destCodePage) == 0)
                $this->pdo->query('SET NAMES \''.$destCodePage.'\' COLLATE \''.$destCodePage.'_general_ci\';');
            else
                $srcCodePage = null;
        } else
            $srcCodePage = null;

		$delka = strlen($content);

		$i = 0;

		$sql = '';
		
		$inserts = 0;
		$others = 0;
		
		$uvozovky1 = 0;
		$uvozovky2 = 0;
		
		while($i < $delka)
		{
			while($content[$i] === '-' && $content[$i+1] === '-' && $uvozovky1 === 0 && $uvozovky2 === 0)
			{
				while ($i < $delka && $content[$i] != self::CRLF)
					$i++;

				$i++;
			}
			
			if($content[$i] === '\'')
			{
				if ($uvozovky1 === 0)
					$uvozovky1 = 1;
				else
					$uvozovky1 = 0;

			} else {
				if ($content[$i] === '"')
					if ($uvozovky2 == 0)
						$uvozovky2 = 1;
					else
						$uvozovky2 = 0;
			}
			
			$sql .= $content[$i];
			if($content[$i] === ';' && $uvozovky1 === 0 && $uvozovky2 === 0)
			{
				$sql = trim($sql, " \n\r\t");
				$res = $this->pdo->query($sql);
				if($res->rowCount() > 0)
					$inserts++;
				else {
					$others++;
				}

				$sql = '';
			}

			$i++;
		}

        if($srcCodePage !== null)
            $this->pdo->query('SET NAMES \''.$srcCodePage.'\' COLLATE \''.$srcCodePage.'_general_ci\';');

		return $i;
	}
}