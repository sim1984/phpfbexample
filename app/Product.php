<?php

namespace App;

use Firebird\Eloquent\Model;

class Product extends Model
{
    /**
     * таблица связанная с моделью
     *
     * @var string
     */
    protected $table = 'PRODUCT';
    
    /**
     * Первичный ключ модели
     *
     * @var string
     */
    protected $primaryKey = 'PRODUCT_ID';    
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;  
    
    /**
     * имя последовательности для генерации первичного ключа
     * @var string 
     */
    protected $sequence = 'GEN_PRODUCT_ID';
      
}

