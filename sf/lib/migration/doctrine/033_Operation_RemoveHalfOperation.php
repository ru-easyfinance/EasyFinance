<?php

/**
 * Operation: Удаляем все парные операции
 */
class Migration033_Operation_RemoveHalfOperation extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        // Ставим первым половинкам правильные знаки и подтягиваем в первую половинку сумму из второй
        $sql = "UPDATE operation o
                LEFT JOIN operation o2 ON o2.id=o.id+1 AND o2.tr_id=o.id
                    SET
                        o.imp_id=ABS(o2.money),
                        o.money = ABS(o.money) * -1
                WHERE
                    o.type=2
                    AND (o.tr_id IS NOT NULL OR o.tr_id <> 0)";

        $this->rawQuery($sql);

        // Удаляем все переводы без сумм
        $sql = "DELETE FROM operation WHERE `type`=2 AND (imp_id IS NULL OR imp_id = 0)";
        $this->rawQuery($sql);

        // Удаляем все операции перевода, без указанного счёта получателя
        $sql = "DELETE FROM operation WHERE `type`=2 AND (transfer=0 OR transfer IS NULL)";
        $this->rawQuery($sql);
    }
}
