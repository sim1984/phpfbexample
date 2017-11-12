<?php

namespace App;

use Firebird\Eloquent\Model;

class Invoice extends Model {

    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'INVOICE';

    /**
     * Первичный ключ модели
     *
     * @var string
     */
    protected $primaryKey = 'INVOICE_ID';

    /**
     * Наша модель не имеет временной метки
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Имя последовательности для генерации первичного ключа
	 *
     * @var string 
     */
    protected $sequence = 'GEN_INVOICE_ID';

    /**
     * Заказачик
	 *
     * @return \App\Customer
     */
    public function customer() {
        return $this->belongsTo('App\Customer', 'CUSTOMER_ID');
    }

    /**
     * Позиции счёт фактуры
	 
     * @return \App\InvoiceLine[]
     */
    public function lines() {
        return $this->hasMany('App\InvoiceLine', 'INVOICE_ID');
    }
    
    /**
     * Оплата 
     */
    public function pay() {
        $connection = $this->getConnection();

        $attributes = $this->attributes;

        $connection->executeProcedure('SP_PAY_FOR_INOVICE', [$attributes['INVOICE_ID']]);
    }
 
}
