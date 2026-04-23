<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

class TCryptConsoleModule extends TConsoleModule implements IConsoleModule
{
    protected const string MODULE_NAME = 'crypt';
    protected const array MODULE_ACTIONS = [
        'encrypt' => 'encrypts input string, syntax: console crypt:encrypt <text_to_encrypt>',
        'decrypt' => 'decrypts input string, syntax: console crypt:decrypt <text_to_decrypt>'
    ];

    /**
     * @throws EConsoleModuleException
     */
    public function execute(string &$output) :int
    {
        if(isset($this->params['action']))
        {
            if(isset($this->params[2]))
            {
                $input = trim($this->params[2]);

                switch ($this->params['action'])
                {
                    case 'encrypt':
                            $crypt = new TCrypt();
                            $output =  $crypt->encrypt($input);

                            return self::SUCCESS_CODE_PRINT;
                        break;

                    case 'decrypt':
                            $crypt = new TCrypt();
                            $output =  $crypt->decrypt($input);

                            return self::SUCCESS_CODE_PRINT;
                        break;

                    case 'help' :
                            $output = $this->help();
                        break;

                    default	: throw new EConsoleModuleException('Action "'.$this->params['action'].'" not valid (available is only encrypt, decrypt, help), see help!', self::ERR_ACTION_NOT_FOUND);
                }
            } else {
                throw new EConsoleModuleException('Not defined input string, see help!', self::ERR_ACTION_NOT_FOUND);
            }
        } else {
            throw new EConsoleModuleException('Action "'.$this->params['action'].'" not defined, see help!', self::ERR_ACTION_NOT_FOUND);
        }

        return self::SUCCESS_CODE_OK;
    }

    public function help() :string
    {
        $actions = '';
        foreach(self::MODULE_ACTIONS as $name => $desc)
            $actions .= "\t" . str_pad($name, 7, ' ').' - '.$desc.PHP_EOL;

        return 	'Actions:'.PHP_EOL.
                 $actions.PHP_EOL;
      }
}


