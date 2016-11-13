<?php

namespace App;

use Firebird\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InvoiceLine extends Model {

    /**
     * таблица связанная с моделью
     *
     * @var string
     */
    protected $table = 'INVOICE_LINE';

    /**
     * Первичный ключ модели
     *
     * @var string
     */
    protected $primaryKey = 'INVOICE_LINE_ID';

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
    protected $sequence = 'GEN_INVOICE_LINE_ID';
    protected $appends = ['SUM_PRICE'];

    public function product() {
        return $this->belongsTo('App\Product', 'PRODUCT_ID');
    }

    public function getSumPriceAttribute() {
        return $this->SALE_PRICE * $this->QUANTITY;
    }

    /**
     * Добавление объекта модели в БД
     * Переопределяем этот метод, т.к. в данном случаем мы работаем с помощью ХП 
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = []) {

        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $connection = $this->getConnection();

        $attributes = $this->attributes;
        
        $connection->executeProcedure('SP_ADD_INVOICE_LINE', [
            $attributes['INVOICE_ID'],
            $attributes['PRODUCT_ID'],
            $attributes['QUANTITY']
        ]);

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Сохранение изменений текущего экземпляра модели в БД
     * Переопределяем этот метод, т.к. в данном случаем мы работаем с помощью ХП 
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performUpdate(Builder $query, array $options = []) {
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            // If the updating event returns false, we will cancel the update operation so
            // developers can hook Validation systems into their models and cancel this
            // operation if the model does not pass validation. Otherwise, we update.
            if ($this->fireModelEvent('updating') === false) {
                return false;
            }

            $connection = $this->getConnection();

            $attributes = $this->attributes;
            
            $connection->executeProcedure('SP_EDIT_INVOICE_LINE', [
                $attributes['INVOICE_LINE_ID'],
                $attributes['QUANTITY']
            ]);            


            $this->fireModelEvent('updated', false);
        }
    }

    /**
     * Удаление текщего экземпляра модели в БД
     * Переопределяем этот метод, т.к. в данном случаем мы работаем с помощью ХП 
     *
     * @return void
     */
    protected function performDeleteOnModel() {

        $connection = $this->getConnection();

        $attributes = $this->attributes;
        
        $connection->executeProcedure('SP_DELETE_INVOICE_LINE', 
            [$attributes['INVOICE_LINE_ID']]);          

    }

}
