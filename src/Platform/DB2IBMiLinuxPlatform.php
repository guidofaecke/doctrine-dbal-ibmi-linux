<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineDbalIbmiLinux\Platform;

use Doctrine\DBAL\Platforms\DB2Platform;

/**
 * IBMi Db2 Schema Manager.
 * More documentation about iSeries schema at https://www-01.ibm.com/support/knowledgecenter/ssw_ibm_i_72/db2/rbafzcatsqlcolumns.htm
 *
 * @author Cassiano Vailati <c.vailati@esconsulting.it>
 * @author James Titcumb <james@asgrim.com>
 * @author Guido Faecke <guido@emcent.com>
 */
class DB2IBMiLinuxPlatform extends DB2Platform
{
    /**
     * {@inheritDoc}
     */
    public function initializeDoctrineTypeMappings()
    {
        $this->doctrineTypeMapping = array(
            'smallint'                => 'smallint',
            'bigint'                  => 'bigint',
            'integer'                 => 'integer',
            'rowid'                   => 'integer',
            'time'                    => 'time',
            'date'                    => 'date',
            'varchar'                 => 'string',
            'character'               => 'string',
            'char'                    => 'string',
            'nvarchar'                => 'string',
            'nchar'                   => 'string',
            'char () for bit data'    => 'string',
            'varchar () for bit data' => 'string',
            'varg'                    => 'string',
            'vargraphic'              => 'string',
            'graphic'                 => 'string',
            'varbinary'               => 'binary',
            'binary'                  => 'binary',
            'varbin'                  => 'binary',
            'clob'                    => 'text',
            'nclob'                   => 'text',
            'dbclob'                  => 'text',
            'blob'                    => 'blob',
            'decimal'                 => 'decimal',
            'numeric'                 => 'float',
            'double'                  => 'float',
            'real'                    => 'float',
            'float'                   => 'float',
            'timestamp'               => 'datetime',
            'timestmp'                => 'datetime',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed): string
    {
        return $fixed ? ($length ? 'CHAR(' . $length . ')' : 'CHAR(255)')
            : ($length ? 'VARCHAR(' . $length . ')' : 'VARCHAR(255)');
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table, $database = null): string
    {
        return "
            SELECT DISTINCT
               c.column_def as default,
               c.table_schem as tabschema,
               c.table_name as tabname,
               c.column_name as colname,
               c.ordinal_position as colno,
               c.type_name as typename,
               c.is_nullable as nulls,
               c.column_size as length,
               c.decimal_digits as scale,
               CASE
                   WHEN c.pseudo_column = 2 THEN 'YES'
                   ELSE 'NO'
               END as identity,
               pk.constraint_type AS tabconsttype,
               pk.key_seq as colseq,
               CASE
                   WHEN c.HAS_DEFAULT = 'J' THEN 1
                   ELSE 0
               END AS autoincrement
             FROM SYSIBM.sqlcolumns as c
             LEFT JOIN
             (
                SELECT
                tc.TABLE_SCHEMA,
                tc.TABLE_NAME,
                tc.CONSTRAINT_TYPE,
                spk.COLUMN_NAME,
                spk.KEY_SEQ
                FROM SYSIBM.TABLE_CONSTRAINTS tc
                LEFT JOIN SYSIBM.SQLPRIMARYKEYS spk
                    ON tc.CONSTRAINT_NAME = spk.PK_NAME AND tc.TABLE_SCHEMA = spk.TABLE_SCHEM AND tc.TABLE_NAME = spk.TABLE_NAME
                WHERE CONSTRAINT_TYPE = 'PRIMARY KEY'
                AND UPPER(tc.TABLE_NAME) = UPPER('" . $table . "')
                ". ($database !== null ? "AND tc.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
             ) pk ON
                c.TABLE_SCHEM = pk.TABLE_SCHEMA
                AND c.TABLE_NAME = pk.TABLE_NAME
                AND c.COLUMN_NAME = pk.COLUMN_NAME
             WHERE
                UPPER(c.TABLE_NAME) = UPPER('" . $table . "')
                ". ($database  !== null ? "AND c.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
             ORDER BY c.ordinal_position
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL($database = null): string
    {
        return "
            SELECT
              DISTINCT NAME
            FROM
                SYSIBM.tables t
            WHERE
              table_type='BASE TABLE'
              ". ($database !== null ? "AND t.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
            ORDER BY NAME
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL($database = null): string
    {
        return "
            SELECT
              DISTINCT NAME,
              TEXT
            FROM QSYS2.sysviews v
            WHERE 1=1
            ". ($database !== null ? "AND v.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
            ORDER BY NAME
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table, $database = null): string
    {
        return  "
            SELECT
                  scc.CONSTRAINT_NAME as key_name,
                  scc.COLUMN_NAME as column_name,
                  CASE
                      WHEN sc.CONSTRAINT_TYPE = 'PRIMARY KEY' THEN 1
                      ELSE 0
                  END AS primary,
                  CASE
                      WHEN sc.CONSTRAINT_TYPE = 'UNIQUE' THEN 0
                      ELSE 1
                  END AS non_unique
              FROM
              QSYS2.syscstcol scc
              LEFT JOIN QSYS2.syscst sc ON
                  scc.TABLE_SCHEMA = sc.TABLE_SCHEMA AND scc.TABLE_NAME = sc.TABLE_NAME AND scc.CONSTRAINT_NAME = sc.CONSTRAINT_NAME
            WHERE scc.TABLE_NAME = UPPER('" . $table . "')
            ". ($database !== null ? "AND scc.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table, $database = null): string
    {
        return "
            SELECT DISTINCT
                fk.COLUMN_NAME AS local_column,
                pk.TABLE_NAME AS foreign_table,
                pk.COLUMN_NAME AS foreign_column,
                fk.CONSTRAINT_NAME AS index_name,
                rc.UPDATE_RULE AS on_update,
                rc.DELETE_RULE AS on_delete
            FROM QSYS2.REFERENTIAL_CONSTRAINTS rc
            LEFT JOIN QSYS2.SYSCSTCOL fk ON
                rc.CONSTRAINT_SCHEMA = fk.CONSTRAINT_SCHEMA AND
                rc.CONSTRAINT_NAME = fk.CONSTRAINT_NAME
            LEFT JOIN QSYS2.SYSCSTCOL pk ON
                rc.UNIQUE_CONSTRAINT_SCHEMA = pk.CONSTRAINT_SCHEMA AND
                rc.UNIQUE_CONSTRAINT_NAME = pk.CONSTRAINT_NAME
            WHERE fk.TABLE_NAME = UPPER('" . $table . "')
            " . ($database !== null ? "AND fk.TABLE_SCHEMA = UPPER('" . $database . "')" : '') . "
        ";
    }

    /**
     * @return string
     */
    public function getListDatabasesSQL(): string
    {
        return "
            SELECT
              DISTINCT TABLE_SCHEMA
            FROM
                SYSIBM.tables t
            ORDER BY TABLE_SCHEMA
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateDatabaseSQL($database): string
    {
        return "CREATE COLLECTION " . $database;
    }

    /**
     * {@inheritDoc}
     */
    protected function doModifyLimitQuery($query, $limit, $offset = null): string
    {
        if ($limit === 0 || $limit === null) {
            return $query;
        }

        if ($offset !== null && $offset > 0) {
            return sprintf('%s LIMIT %s OFFSET %s', $query, $limit, $offset);
        }

        return sprintf('%s LIMIT %s', $query, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatString(): string
    {
        return 'Y-m-d-H.i.s.u';
    }
}
