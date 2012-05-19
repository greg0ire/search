<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\RecordFilterBundle\Tests\Record;

use Rollerworks\RecordFilterBundle\Record\Sql\WhereBuilder;

/**
 * Test the Validation generator. Its work is generating on-the-fly subclasses of a given model.
 * As you may have guessed, this is based on the Doctrine\ORM\Proxy module.
 */
class SQLTest extends OrmTestCase
{
    /**
     * @dataProvider provideBasicsTests
     *
     * @param $filterQuery
     * @param $expectedSql
     */
    function testBasics($filterQuery, $expectedSql)
    {
        $input = $this->newInput($filterQuery);
        $this->assertTrue($this->formatter->formatInput($input));

        $whereBuilder = new WhereBuilder($this->em);
        $whereCase = $this->cleanSql($whereBuilder->getWhereClause($input->getFieldsConfig(), $this->formatter));

        $this->assertEquals($expectedSql, $whereCase);
    }

    static public function provideBasicsTests()
    {
        return array(
            array('invoice_customer=2;', '(customer IN(2))'),
            array('invoice_label=F2012-4242;', '(label IN(\'F2012-4242\'))'),
            array('invoice_customer=2, 5;', '(customer IN(2, 5))'),
            array('invoice_customer=2-5;', '((customer BETWEEN 2 AND 5))'),
            array('invoice_customer=2-5, 8;', '(customer IN(8) AND (customer BETWEEN 2 AND 5))'),
            array('invoice_customer=2-5,!8-10;', '((customer BETWEEN 2 AND 5) AND (customer NOT BETWEEN 8 AND 10))'),
            array('invoice_customer=2-5, !8;', '(customer NOT IN(8) AND (customer BETWEEN 2 AND 5))'),
            array('invoice_customer=2-5, >8;', '((customer BETWEEN 2 AND 5) AND customer > 8)'),

            array('(invoice_customer=2;),(invoice_customer=3;)', '(customer IN(2)) OR (customer IN(3))'),
            array('(invoice_customer=2,3;),(invoice_customer=3,5;)', '(customer IN(2, 3)) OR (customer IN(3, 5))'),
            array('(invoice_customer=2,3; invoice_status=Active;),(invoice_customer=3,5;)', '(customer IN(2, 3) AND status IN(1)) OR (customer IN(3, 5))'),

            // Expects empty as there is no field with that name
            array('(user=2;),(user=2;)', ''),
        );
    }
}
