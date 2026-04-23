<?php

/**
 * Třída základních konstant
 *
 * @name TConsts
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 */

const PHP_TAB = "\t";

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Lang' .DIRECTORY_SEPARATOR.'TLang.php';

class TConsts
{
	const string utf8				= 'utf-8';
	const string windows1250		= 'windows-1250';

	const int SCREEN_RESOLUTION_MIN = 1199;

	const int PING_INTERVAL		= 15;	// pocet sekund pingu
	const int PING_HISTORY		= 5;	// pocet dnu historie pingu
	const int AJAX_TIMEOUT		= 15000;

	const int DESC_SUBSTRING_LENGTH = 100;
	const int NAME_SUBSTRING_LENGTH = 43;

	const string BLANK			= '_blank';

	const int MAX_TIME_LIMIT	= 2000;	// 2000 sekund
	const string MAX_FILE_SIZE 	= '32M';// 16777216; // maximalne 16 MB
	const string MAX_POST_SIZE 	= '64M';

	const int LIMIT_PAGE_SMALL	= 25;
	const int LIMIT_PAGE		= 50;
	const int LIMIT_LOGZURNAL	= 100;

	const int NO  				= 0;
	const int YES 				= 1;

	const string STATUS_ALL		= ':all';
	const string STATUS_WORK	= ':work';
	const int STATUS_ACTIVE 	= 1;
	const int STATUS_INACTIVE 	= 0;

	const int STATE_ONLINE		= 1;
	const int STATE_OFFLINE		= 0;

	const int NOTICE_TYPE_INFO		= 1;
	const int NOTICE_TYPE_WARNING	= 10;
	const int NOTICE_TYPE_ERROR		= 100;

	const int NOTICE_STATUS_DENIED	= -1;
	const int NOTICE_STATUS_NEW		= 0;
	const int NOTICE_STATUS_READ	= 1;
	const int NOTICE_STATUS_WORK	= 10;
	const int NOTICE_STATUS_READY	= 100;

	const int LOG_STATE_ERROR		= -1;
	const int LOG_STATE_WARNING		= 0;
	const int LOG_STATE_OK			= 1;

	const int LANG_DEFAULT	= 0;
	const int LANG_CS		= 1;
	const int LANG_EN		= 2;
	const int LANG_DE		= 3;
	const int LANG_SK		= 4;
	const int LANG_PL		= 5;
	const int LANG_RU		= 6;
	const int LANG_FR		= 7;

	const string CR			= "\r";
	const string LF			= "\n";
	const string CRLF 		= "\r\n";
	const string TAB		= "\t";

	public static function __ALL() :string
	{
		return '- '.__('box.all', 'All').' -';
	}

	public static function __MY() :string
	{
		return '- '.__('box.my', 'My').' -';
	}
	
	public static function CodeStatuses() :array
	{
		return [
                TUSer::CODE_STATUS_INACTIVE 	     => ['name'=>__('box.verification_code.inactive', 'Inactive')],
				TUSer::CODE_STATUS_ACTIVE_EMAIL 	 => ['name'=>__('box.verification_code.active.email', 'E-mail')],
                TUSer::CODE_STATUS_PROXY_EMAIL       => ['name'=>__('box.verification_code.proxy', 'E-mail (proxy only)')],
                TUSer::CODE_STATUS_ACTIVE_AUTHENTICATOR  => ['name'=>__('box.verification_code.active.microsoft', 'Authenticator')],
                TUSer::CODE_STATUS_PROXY_AUTHENTICATOR   => ['name'=>__('box.verification_code.proxy.microsoft', 'Authenticator (proxy only)')],
		];
	}
	
	public static function optionsCodeStatuses(array $result = [], string $column = 'name') :array
	{
		foreach(self::CodeStatuses() as $code_status_id => $record)
			$result[$code_status_id] = $record[$column];
			
		return $result;
	}
	
	public static function getCodeStatus(int $code_status_id): array
    {
		return self::CodeStatuses()[$code_status_id];
	}
	
	public static function columnCodeStatus(int $code_status_id, string $column = 'name')
	{
		return self::CodeStatuses()[$code_status_id][$column];
	}

	public static function NoticeTypes() :array
	{
		return [
			self::NOTICE_TYPE_INFO 		=> ['name'=>__('notice.type.info', 'Information'),	'class'=>'ico-info'],
			self::NOTICE_TYPE_WARNING	=> ['name'=>__('notice.type.warning', 'Warning'),	'class'=>'ico-wrn'],
			self::NOTICE_TYPE_ERROR		=> ['name'=>__('notice.type.error', 'Error'),		'class'=>'ico-err']
		];
	}

	public static function getNoticeType(?int $notice_type_id, string $column = 'name')
	{
		$zal = self::NoticeTypes();
		return $zal[$notice_type_id][$column];
	}

	public static function optionsNoticeTypes(array $result = [], string $column = 'name')
	{
		foreach(self::NoticeTypes() as $notice_type_id => $zaznam)
			$result[$notice_type_id] = $zaznam[$column];

		return $result;
	}

	public static function optionsFilterNoticeTypes(array $result = [], string $column = 'name')
	{
		$result[self::STATUS_ALL] = '- '.__('box.all', 'All').' -';

		foreach(self::NoticeTypes() as $notice_type_id => $zaznam)
			$result[':'.$notice_type_id] = $zaznam[$column];

		return $result;
	}

	public static function getNoticeTypesIco(?int $notice_type_id) :string
	{
		$zal = self::NoticeTypes();
		return '<span class="'.$zal[$notice_type_id]['class'].'" title="'.$zal[$notice_type_id]['name'].'"></span>';
	}

	public static function NoticeRights() :array
	{
		return [
			TRights::ADMIN		    => ['name'=>__('right.administrators', 'Administrators')],
			TRightsEx::MANAGER	    => ['name'=>__('right.managers', 'System managers')],
			TRightsEx::MANAGER_UNIT	=> ['name'=>__('right.unit_controllers', 'Unit controllers')],
		];
	}
	public static function optionsNoticeRights(array $result = [], string $column = 'name') :array
	{
		foreach(self::NoticeRights() as $notice_right_id => $zaznam)
			$result[$notice_right_id] = $zaznam[$column];

		return $result;
	}

	public static function optionsFilterNoticeRights(array $result = [], string $column = 'name') :array
	{
		$result[self::STATUS_ALL] = '- '.__('box.all', 'Vše').' -';
		foreach(self::NoticeRights() as $notice_right_id => $zaznam)
			$result[$notice_right_id] = $zaznam[$column];

		return $result;
	}

	public static function getNoticeRight(?int $notice_right_id, string $column) :string
	{
		$zal = self::NoticeRights();
		return $zal[$notice_right_id][$column];
	}

	public static function NoticeStatuses() :array
	{
		return [
			self::NOTICE_STATUS_NEW		=> ['name'=>__('notice.status.new', 'New'),			'class'=>'status-silver'],
			self::NOTICE_STATUS_READ	=> ['name'=>__('notice.status.read', 'Read'),		'class'=>'status-blue'],
			self::NOTICE_STATUS_WORK	=> ['name'=>__('notice.status.work', 'Working'),	'class'=>'status-orange'],
			self::NOTICE_STATUS_READY	=> ['name'=>__('notice.status.ready', 'Ready'),		'class'=>'status-green'],
			self::NOTICE_STATUS_DENIED	=> ['name'=>__('notice.status.denied', 'Denied'),	'class'=>'status-red'],

		];
	}

	public static function getNoticeStatus(?int $notice_status_id, string $column = 'name') :string
	{
		$zal = self::NoticeStatuses();
		return $zal[$notice_status_id][$column];
	}

	public static function optionsNoticeStatuses(array $result = [], string $column = 'name') :array
	{
		foreach(self::NoticeStatuses() as $notice_status_id => $zaznam)
			$result[$notice_status_id] = $zaznam[$column];

		return $result;
	}

	public static function optionsFilterNoticeStatuses(array $result = [], string $column = 'name') :array
	{
		$result[self::STATUS_ALL] = '- '.__('box.all', 'All').' -';
		foreach(self::NoticeStatuses() as $notice_status_id => $zaznam)
			$result[':'.$notice_status_id] = $zaznam[$column];

		return $result;
	}

	public static function getNoticeStatusesColumn(?int $notice_status_id, string $column = 'class') :string
	{
		$zal = self::NoticeStatuses();
		return $zal[$notice_status_id][$column];
	}

	public static function Languages() :array
	{
		return [
			self::LANG_DEFAULT	=> ['name'=>__('lang.default', 'Default (English)'),	'file'=>null,	'class'=>''],
			/*self::LANG_CS		=> ['name'=>__('lang.czech', 'Čeština'), 		'file'=>'TLangCS.php',	'class'=>'CS'],
			self::LANG_EN		=> ['name'=>__('lang.english', 'English'), 		'file'=>'TLangEN.php',	'class'=>'EN'],
			self::LANG_DE		=> ['name'=>__('lang.german', 'Deutsch'), 		'file'=>'TLangDE.php',	'class'=>'DE'],
			self::LANG_SK		=> ['name'=>__('lang.slovak', 'Slovenčina'),	'file'=>'LangSK.php',	'class'=>'SK'],
			self::LANG_PL		=> ['name'=>__('lang.polish', 'Polski'), 		'file'=>'LangPL.php',	'class'=>'PL'],
			self::LANG_RU		=> ['name'=>__('lang.russian', 'Pусский'), 		'file'=>'LangRU.php',	'class'=>'RU'],
			self::LANG_FR		=> ['name'=>__('lang.french', 'French'),	 	'file'=>'LangFR.php',	'class'=>'FR'],*/
		];
	}

	public static function getLanguage(?int $lang_id) :array
	{
		$langs = self::Languages();
        if(key_exists($lang_id, $langs))
		    return $langs[$lang_id];
        else
            return $langs[self::LANG_DEFAULT];
	}

	public static function getLanguageFileName(?int $lang_id) :?string
	{
		$languages = self::Languages();
		if(key_exists($lang_id, $languages))
			return $languages[$lang_id]['file'];
		else
			return $languages[self::LANG_DEFAULT]['file'];
	}

	public static function getLanguageClass(?int $lang_id) :?string
	{
		$languages = self::Languages();
		if(key_exists($lang_id, $languages))
			return $languages[$lang_id]['class'];
		else
			return $languages[self::LANG_DEFAULT]['class'];
	}

	public static function optionsLanguages(array $result = [], string $columns = 'name') :array
	{
		foreach(self::Languages() as $lang_id => $lang)
			$result[$lang_id] = $lang[$columns];

		return $result;
	}

	public static function Statuses() :array
	{
		return [
			self::STATUS_ACTIVE 	=> ['name' => __('box.active', 'Active')],
			self::STATUS_INACTIVE	=> ['name' => __('box.inactive', 'Inactive')]
		];
	}

	public static function optionsStatuses(array $result = []) :array
	{
		foreach(self::Statuses() as $status_id => $item)
			$result[$status_id] = $item['name'];

		return $result;
	}

	public static function optionsFilterStatuses(array $result = []) :array
	{
		$result[self::STATUS_ALL]			= '- '.__('box.all', 'All').' -';
		$result[':'.self::STATUS_ACTIVE]	= __('box.active_only', 'Only active');
		$result[':'.self::STATUS_INACTIVE]	= __('box.inactive_only', 'Only incative');

		return $result;
	}

	public static function getStatusIco(?int $status_id) :string
	{
		switch($status_id)
		{
			case '-100'	: $class = 'status-black';	$title = __('status.dead', 'Dead'); break;
			case '-1'	: $class = 'status-red';	$title = __('status.error', 'Error'); break;
			case '1'	: $class = 'status-green';	$title = __('status.active', 'Active'); break;
			case '0'	:
			default		: $class = 'status-silver';	$title = __('status.disabled', 'Disabled'); break;
		}

		return '<span class="'.$class.'" title="'.$title.'"></span>';
	}

	public static function States() :array
	{
		return [
			self::STATE_ONLINE 	=> ['name'=>__('box.online', 'online')],
			self::STATE_OFFLINE	=> ['name'=>__('box.offline', 'offline')]
		];
	}

	public static function getStates(array $result = []) :array
	{
		foreach(self::States() as $state_id => $item)
			$result[$state_id] = $item['name'];

		return $result;
	}

	public static function optionsFilterStates(array $result = []) :array
	{
		$result[self::STATUS_ALL]		= '- '.__('box.all', 'Vše').' -';
		$result[':'.self::STATE_ONLINE]	= __('box.online_only', 'Pouze přihlášení');
		$result[':'.self::STATE_OFFLINE]= __('box.offline_only', 'Pouze odhlášení');

		return $result;
	}

	public static function getStateIco(?int $status_id) :string
	{
		switch($status_id)
		{
			case self::STATE_ONLINE		: $class = 'status-green'; $title = __('box.online', 'online'); break;
			case self::STATE_OFFLINE	:
			default						: $class = 'status-silver'; $title = __('box.offline', 'offline'); break;
		}

		return '<span class="'.$class.'" title="'.$title.'"></span>';
	}

	public static function LogStates() :array
	{
		return [
			self::LOG_STATE_OK 		=> ['name' => __('alt.ok', 'OK'), 'class'=>'status-green'],
			self::LOG_STATE_ERROR	=> ['name' => __('alt.error', 'Error'), 'class'=>'status-red'],
			self::LOG_STATE_WARNING	=> ['name' => __('alt.warning', 'Warning'), 'class'=>'status-yellow']
		];
	}

	public static function getLogStateColumn(?int $state_id, string $column = 'name') :string
	{
		$log_states = self::LogStates();
		if($state_id < 0)
			$state_id = self::LOG_STATE_ERROR;
		else
			if($state_id > 0)
				$state_id = self::LOG_STATE_OK;

		return $log_states[$state_id][$column];
	}

	public static function getLogStateIco(?int $state_id) :string
	{
		$log_states = self::LogStates();
		if($state_id > 0)
			$log_state = $log_states[self::LOG_STATE_OK];
		else
			if($state_id < 0)
				$log_state = $log_states[self::LOG_STATE_ERROR];
			else
				$log_state = $log_states[self::LOG_STATE_WARNING];

		return '<span class="'.$log_state['class'].'" title="'.$log_state['name'].'"></span>';
	}

	public static function YesNo() :array
	{
		return [
			self::YES 	=> __('box.yes', 'Ano'),
			self::NO	=> __('box.no', 'Ne')
		];
	}

	public static function optionsYesNo(array $result = []) :array
	{
		return $result + self::YesNo();
	}

    public static function optionsFilterYesNo(array $result = []) :array
    {
        $result[self::STATUS_ALL] = '- '.__('box.all', 'Vše').' -';
        $result[':'.self::YES] = __('box.yes', 'Ano');
        $result[':'.self::NO]  = __('box.no', 'Ne');

        return $result;
    }

	public static function getYesNoText($value) :string
	{
		$zal = self::YesNo();

		if($value == '1' || $value === true)
			return $zal[self::YES];
		else
			return $zal[self::NO];
	}

    public static function langYesNo($value, &$lang)
    {
        if($value == '1' || $value === true)
            return $lang->__('box.yes','Ano');
        else
            return $lang->__('box.no','Ne');
    }

	public static function Designations() :array
	{
		return ['ml.','st.','nejml.','nejst.','I.','II.','III.','IV.','V.','VI.'];
	}

	public static function optionsDesignations(array $result = [''=>'']) :array
	{
		foreach(self::Designations() as $value)
			$result[$value] = $value;

		return $result;
	}

	public static function TitlesBefore() :array
	{
		return [
			'Bc.', 'BcA.',
			'Ing.', 'Ing. arch.', 'Mgr.', 'MgrA.',
			'JUC.', 'JUDr.', 'ICDr.', 'MDDr.', 'MSDr.', 'MUC.', 'MVC.', 'MUDr.', 'MVDr.',
			'PeadDr.', 'PharmDr.', 'PhDr.', 'PhMr.', 'RNDr.', 'RSDr.', 'RCDr.', 'RTDr.', 'ThDr.', 'ThLic.', 'ThMgr.',
			'doc.', 'prof.'
		];
	}

	public static function optionsTitlesBefore(array $result = [''=>'']) :array
	{
		foreach(self::TitlesBefore() as $item)
			$result[$item] = $item;

		return $result;
	}

	public static function TitlesAfter() :array
	{
		return ['DiS.', 'Ph.D.', 'Dr.', 'Th.D.', 'CSc.', 'DrSc.', 'DSc.', 'BA', 'MBA', 'DBA'];
	}

	public static function optionsTitlesAfter(array $result = [''=>'']) :array
	{
		foreach(self::TitlesAfter() as $item)
			$result[$item] = $item;

		return $result;
	}

	public static function optionsFilterChecked(array $result = []) :array
	{
		$result[':all']	= '- '.__('box.all', 'Vše').' -';
		$result[':1'] 	= __('box.checked_only', 'Pouze zařazené');
		$result[':0']	= __('box.unchecked_only', 'Pouze nezařazené');

		return $result;
	}

	public static function getCheckedIco(?int $status_id, int $classSize = 24, int $size = 24) :string
	{
		$title = '';
		switch($status_id)
		{
			case '-100'	: $pic = 'forbid'.$size.'_black.png'; break;
			case '-1'	: $pic = 'forbid'.$size.'.png'; break;
			case '1'	: $pic = 'checked'.$size.'.png'; break;
			case '0'	:
			default		: $pic = 'checked'.$size.'_silver.png'; break;
		}

		return '<img src="'.TUrl::images.$pic.'" title="'.$title.'" class="ico'.$classSize.'">';
	}

	public static function optionsEject() :array
	{
		return [
			''		=>	__('box.never', 'Nikdy'),
			'5'		=>	sprintf(__('box.after_minutes', 'po %s minutách'), 5),
			'10'	=>	sprintf(__('box.after_minutes', 'po %s minutách'), 10),
			'15'	=>	sprintf(__('box.after_minutes', 'po %s minutách'), 15),
			'30'	=>	sprintf(__('box.after_minutes', 'po %s minutách'), 30),
			'60'	=>	sprintf(__('box.after_hour', 	'po %s hodině'), 1),
			'120'	=>	sprintf(__('box.after_hours',	'po %s hodinách'), 2),
			'240'	=>	sprintf(__('box.after_hours', 	'po %s hodinách'), 4),
			'480'	=>	sprintf(__('box.after_hours', 	'po %s hodinách'), 8),
		];
	}

	public static function FileTypes() :array
	{
        return [
            'xls' 	=> ['class' => 'ft-xls', 	'name' => 'MS Excel', 			    'mime'=>'application/vnd.ms-excel'],
            'xlsm'	=> ['class' => 'ft-xls',	'name' => 'MS Excel + makro', 	    'mime'=>'application/vnd.ms-excel'],
            'xlsx'	=> ['class' => 'ft-xlsx', 	'name' => 'MS Excel', 			    'mime'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ods'   => ['class' => 'ft-ods',    'name' => 'OpenDocument tabulka',   'mime' => 'application/vnd.oasis.opendocument.spreadsheet'],

            'doc' 	=> ['class' => 'ft-doc', 	'name' => 'MS Word', 			'mime'=>'application/msword'],
            'docx'	=> ['class' => 'ft-docx', 	'name' => 'MS Word', 			'mime'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'docm'  => ['class' => 'ft-docm',   'name' => 'Word dokument s makry',  'mime' => 'application/vnd.ms-word.document.macroEnabled.12'],
            'odt'   => ['class' => 'ft-odt',    'name' => 'OpenDocument text',      'mime' => 'application/vnd.oasis.opendocument.text'],
            'rtf' 	=> ['class' => 'ft-rtf', 	'name' => 'Rightext MS Windows','mime'=>'application/rtf'],

            'ppt' 	=> ['class' => 'ft-ppt', 	'name' => 'MS PowerPoint', 		'mime'=>'application/vnd.ms-powerpoint'],
            'pptx'	=> ['class' => 'ft-pptx', 	'name' => 'MS PowerPoint', 		'mime'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'pptm'  => ['class' => 'ft-pptm',   'name' => 'PowerPoint prezentace s makry',  'mime'=>'application/vnd.ms-powerpoint.presentation.macroEnabled.12'],
            'odp'   => ['class' => 'ft-odp',    'name' => 'OpenDocument prezentace',        'mime'=>'application/vnd.oasis.opendocument.presentation'],

            'mdb' 	=> ['class' => 'ft-mdb', 	'name' => 'MS Access', 			'mime'=>'application/x-msaccess'],
            'mdbx'	=> ['class' => 'ft-mdbx', 	'name' => 'MS Access', 			'mime'=>'application/x-msaccess'],

            'zip'	=> ['class' => 'ft-zip', 	'name' => 'Archive WinZip',		'mime'=>'application/x-zip-compressed'],
            '7z'	=> ['class' => 'ft-7z',  	'name' => 'Archive 7zip', 		'mime'=>'application/x-7z-compressed'],
            'rar'	=> ['class' => 'ft-rar', 	'name' => 'Archive RAR', 		'mime'=>'application/vnd.rar'],
            'gz'	=> ['class' => 'ft-gz',  	'name' => 'Archive gz', 		'mime'=>'application/gzip'],
            'tar'   => ['class' => 'ft-tar',    'name' => 'TAR archiv',         'mime'=>'application/x-tar'],

            'bmp'	=> ['class' => 'ft-bmp', 	'name' => 'Windows Bitmap',		'mime'=>'image/bmp'],
            'png'	=> ['class' => 'ft-png', 	'name' => 'Picture PNG', 		'mime'=>'image/png'],
            'jpg'	=> ['class' => 'ft-jpg', 	'name' => 'Picture JPG', 		'mime'=>'image/jpeg'],
            'jpeg'	=> ['class' => 'ft-jpg',	'name' => 'Picture JPEG', 		'mime'=>'image/jpeg'],
            'gif'	=> ['class' => 'ft-gif', 	'name' => 'Picture GIF', 		'mime'=>'image/gif'],
            'tif'   => ['class' => 'ft-tiff',   'name' => 'Obrázek TIFF',       'mime' => 'image/tiff'],
            'tiff'  => ['class' => 'ft-riff',   'name' => 'Obrázek TIFF',       'mime' => 'image/tiff'],
            'webp'  => ['class' => 'ft-webp',   'name' => 'Obrázek WebP',       'mime' => 'image/webp'],
            'heic'  => ['class' => 'ft-heic',   'name' => 'Obrázek HEIC (Apple)',   'mime' => 'image/heic'],

            'pdf' 	=> ['class' => 'ft-pdf', 	'name' => 'Adobe PDF', 			'mime'=>'application/pdf'],
            'indd'	=> ['class' => 'ft-indd',	'name' => 'Adobe InDesign', 	'mime'=>'application/indesign,application/x-indesign'],
            'ai'	=> ['class' => 'ft-ai',  	'name' => 'Adobe Illustrator',	'mime'=>'application/illustrator,application/x-illustrator'],
            'psd'	=> ['class' => 'ft-psd', 	'name' => 'Adobe Photoshop',	'mime'=>'application/psd,application/x-psd'],
            'ps'	=> ['class' => 'ft-ps',  	'name' => 'PostScriptu', 		'mime'=>'application/postscript'],
            'eps'	=> ['class' => 'ft-eps', 	'name' => 'PostScriptu', 		'mime'=>'application/postscript'],

            'dwg'	=> ['class' => 'ft-dwg', 	'name' => 'AutoCAD', 			'mime'=>'application/dwg,application/x-dwg'],
            'dxf'	=> ['class' => 'ft-dxf', 	'name' => 'AutoCAD', 			'mime'=>'application/dwg,application/x-dxf'],
            'cdr'	=> ['class' => 'ft-cdr', 	'name' => 'CorelDraw', 			'mime'=>'application/cdr,application/x-dwg'],

            'msg'	=> ['class' => 'ft-msg', 	'name' => 'MS Outlook', 		'mime'=>'application/dwg,application/x-msg'],
            'eml'	=> ['class' => 'ft-eml', 	'name' => 'Mail file',			'mime'=>'application/cdr,application/x-eml'],
            'pst'   => ['class' => 'ft-pst',    'name' => 'Outlook datový soubor (PST)',    'mime' => 'application/vnd.ms-outlook-pst'],
            'ost'   => ['class' => 'ft-ost',    'name' => 'Outlook offline složka (OST)',   'mime' => 'application/vnd.ms-outlook-ost'],

            'log'   => ['class' => 'ft-log',    'name' => 'Log soubor',         'mime' => 'text/plain'],
            'md'    => ['class' => 'ft-markdown', 'name' => 'Markdown dokument','mime' => 'text/markdown'],
            'xhtml' => ['class' => 'ft-xhtml',  'name' => 'XHTML stránka',      'mime' => 'application/xhtml+xml'],
            'html'  => ['class' => 'ft-html',   'name' => 'HTML stránka',       'mime' => 'text/html'],
            'sgml'  => ['class' => 'ft-sqml',   'name' => 'SGML dokument',      'mime' => 'text/sgml'],
            'yaml'  => ['class' => 'ft-yaml',   'name' => 'YAML soubor',        'mime' => 'application/x-yaml'],
            'yml'   => ['class' => 'ft-yml',    'name' => 'YAML soubor',        'mime' => 'application/x-yaml'],
            'ini'   => ['class' => 'ft-ini',    'name' => 'Konfigurační soubor INI',    'mime' => 'text/plain'],

            'txt' 	=> ['class' => 'ft-txt', 	'name' => 'Plain text',			'mime'=>'text/plain'],
            'xml'	=> ['class' => 'ft-xml',	'name' => 'XML file',			'mime'=>'application/xml'],
            'xslt'	=> ['class' => 'ft-xml',	'name' => 'XSLT file',			'mime'=>'application/xslt+xml'],
            'svg'	=> ['class' => 'ft-svg',	'name' => 'SVG file',			'mime'=>'image/svg+xml'],
            'csv'	=> ['class' => 'ft-csv',	'name' => 'CSV file',			'mime'=>'text/csv'],
            'json'	=> ['class' => 'ft-json',	'name' => 'JSON file',			'mime'=>'application/json'],
            'tsv'   => ['class' => 'ft-tsv',    'name' => 'TSV soubor',         'mime' => 'text/tab-separated-values'],

            'accdb' => ['class' => 'ft-accdb',  'name' => 'MS Access databáze (ACCDB)',       'mime' => 'application/vnd.ms-access'],
            'sqlite'=> ['class' => 'ft-sqlite', 'name' => 'SQLite databáze',                  'mime' => 'application/vnd.sqlite3'],
            'db'    => ['class' => 'ft-db',     'name' => 'Obecný databázový soubor (DB)',    'mime' => 'application/octet-stream'],
            'sql'   => ['class' => 'ft-sql',    'name' => 'SQL skript / dump',                'mime' => 'application/sql'],

            'mp4'   => ['class' => 'ft-mp4',    'name' => 'Video MP4',              'mime' => 'video/mp4'],
            'mp5'   => ['class' => 'ft-mp5',    'name' => 'Video MP5',              'mime' => 'video/mp5'],
            'mpg'	=> ['class' => 'ft-mpg', 	'name' => 'Video MPEG-2',		    'mime'=>'video/mpeg'],
            'mpeg'	=> ['class' => 'ft-mpg',	'name' => 'Video MPEG-2', 		    'mime'=>'video/mpeg'],
            'avi'   => ['class' => 'ft-avi',    'name' => 'Video AVI',              'mime' => 'video/x-msvideo'],
            'mov'   => ['class' => 'ft-mov',    'name' => 'Video QuickTime (MOV)',  'mime' => 'video/quicktime'],
            'wmv'   => ['class' => 'ft-wmv',    'name' => 'Video Windows Media',    'mime' => 'video/x-ms-wmv'],
            'flv'   => ['class' => 'ft-flv',    'name' => 'Video Flash (FLV)',      'mime' => 'video/x-flv'],
            'mkv'   => ['class' => 'ft-mkv',    'name' => 'Video Matroska (MKV)',   'mime' => 'video/x-matroska'],
            'webm'  => ['class' => 'ft-webm',   'name' => 'Video WebM',             'mime' => 'video/webm'],

            'mp3'	=> ['class' => 'ft-mp3',	'name' => 'Music MP3', 			'mime'=>'audio/mpeg'],

            'isdoc'	 => ['class' => 'ft-isdoc',	'name' => 'ISDOC file',			'mime'=>'application/xml'],
            'isdocx' => ['class' => 'ft-isdoc',	'name' => 'ISDOCX file',		'mime'=>'application/xml'],
            'isdocp' => ['class' => 'ft-isdoc',	'name' => 'ISDOCP file',		'mime'=>'application/xml'],
            'isdoctr'=> ['class' => 'ft-isdoc',	'name' => 'ISDOCTR file',		'mime'=>'application/xml']
        ];
	}

	public static function getMimeTypeIco(?string $extension) :string
	{
		$extension = mb_strtolower($extension);

		$types = self::FileTypes();
		if(key_exists($extension, $types))
		{
			$class = $types[$extension]['class'];
			$title = $types[$extension]['name'];
		} else {
			$class = 'ft-unknown';
			$title = 'Unknown';
		}

        return '<span class="'.$class.'" title="'.$title.'"></span>';
	}

	public static function getMimeTypeColumn(?string $extension, string $column = 'class') :string
	{
		$extension = mb_strtolower($extension);

		$types = self::FileTypes();
		if(key_exists($extension, $types))
			return $types[$extension][$column];
		else
			return 'ft-unknown';
	}

	public static function getMimeType(?string $extension) :string
	{
		$extension = mb_strtolower($extension);

		$types = self::FileTypes();
		if(key_exists($extension, $types))
			return $types[$extension]['mime'];
		else
			return 'application/octet-stream';
	}
}