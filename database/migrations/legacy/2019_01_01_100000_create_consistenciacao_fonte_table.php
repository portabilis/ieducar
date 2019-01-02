<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateConsistenciacaoFonteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            '
                SET default_with_oids = true;
                
                CREATE SEQUENCE consistenciacao.fonte_idfon_seq
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 0
                    NO MAXVALUE
                    CACHE 1;

                CREATE TABLE consistenciacao.fonte (
                    idfon integer DEFAULT nextval(\'consistenciacao.fonte_idfon_seq\'::regclass) NOT NULL,
                    nome character varying(60) NOT NULL,
                    situacao character(1) NOT NULL,
                    CONSTRAINT ck_fonte_situacao CHECK (((situacao = \'A\'::bpchar) OR (situacao = \'I\'::bpchar)))
                );
                
                ALTER TABLE ONLY consistenciacao.fonte
                    ADD CONSTRAINT pk_fonte PRIMARY KEY (idfon);

                SELECT pg_catalog.setval(\'consistenciacao.fonte_idfon_seq\', 1, false);
            '
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consistenciacao.fonte');

        DB::unprepared('DROP SEQUENCE consistenciacao.fonte_idfon_seq;');
    }
}