<?php
class UserLoginModel extends CommonModel
{
    public $table = 'user';
    public function __construct()
    {
        parent::__construct();
    }

    public function setRules()
    {
        $this->form_validation->set_rules('email', 'Email required', 'required');
    }
}
