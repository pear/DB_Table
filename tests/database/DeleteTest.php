<?php
require_once dirname(__FILE__) . '/DatabaseTest.php';

class DeleteTest extends DatabaseTest 
{
    var $insert = false;


    function testDeleteRef1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Delete reference Address => Street";
        }
        $db =& $this->db;

        $result = $db->deleteRef('Address', 'Street');
        $this->assertNotError($result);
         
        $ref = $db->getRef();
        $this->assertNotError($ref);
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        $this->assertNotError($ref_to);
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        $this->assertNotError($link);
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
    }

    function testDeleteRef2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Delete reference PersonAddress => Person";
        }
        $db =& $this->db;

        $result = $db->deleteRef('PersonAddress', 'Person');
        $this->assertNotError($result);
 
        $ref = $db->getRef();
        $this->assertNotError($ref);
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        $this->assertNotError($ref_to);
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        $this->assertNotError($link);
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
            
    }

    function testDeleteTable1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Delete Table Person";
        }
        $db =& $this->db;

        $result = $db->deleteTable('Person');
        $this->assertNotError($result);

        $table = $db->getTable();
        $this->assertNotError($table);
        if ($this->verbose > 0) {
            print "\n\nTable: ";
            $s = array();
            foreach ($table as $name => $def) {
                $s[] = $name;
            }
            print implode(', ', $s);
        }

        $col = $db->getCol();
        $this->assertNotError($col);
        if ($this->verbose > 0) {
            print "\n\nCol:";
            foreach ($col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . implode(', ', $s) . ')';
            }
        }

        $foreign_col = $db->getForeignCol();
        $this->assertNotError($foreign_col);
        if ($this->verbose > 0) {
            print "\n\nForeignCol:";
            foreach ($foreign_col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . 
                      implode(', ', $s) . ')';
            }
        }

        $ref = $db->getRef();
        $this->assertNotError($ref);
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        $this->assertNotError($ref_to);
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . 
                      implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        $this->assertNotError($link);
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
            
    }

    function testDeleteTable2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Delete Table PersonAddress";
        }
        $db =& $this->db;

        $result = $db->deleteTable('PersonAddress');
        $this->assertNotError($result);
         
        $table = $db->getTable();
        $this->assertNotError($table);
        if ($this->verbose > 0) {
            print "\n\nTable: ";
            $s = array();
            foreach ($table as $name => $def) {
                $s[] = $name;
            }
            print implode(', ', $s);
        }

        $col = $db->getCol();
        $this->assertNotError($col);
        if ($this->verbose > 0) {
            print "\n\nCol:";
            foreach ($col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . implode(', ', $s) . ')';
            }
        }

        $foreign_col = $db->getForeignCol();
        $this->assertNotError($foreign_col);
        if ($this->verbose > 0) {
            print "\n\nForeignCol:";
            foreach ($foreign_col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . 
                      implode(', ', $s) . ')';
            }
        }

        $ref = $db->getRef();
        $this->assertNotError($ref);
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        $this->assertNotError($ref_to);
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . 
                      implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        $this->assertNotError($link);
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
    }

    function testDeleteTable3()
    {
        if ($this->verbose > 0) {
            print "\n" . "Delete Table Address";
        }
        $db =& $this->db;

        $result = $db->deleteTable('Address');
        $this->assertNotError($result);
 
        $table = $db->getTable();
        $this->assertNotError($table);
        if ($this->verbose > 0) {
            print "\n\nTable: ";
            $s = array();
            foreach ($table as $name => $def) {
                $s[] = $name;
            }
            print implode(', ', $s);
        }

        $col = $db->getCol();
        $this->assertNotError($col);
        if ($this->verbose > 0) {
            print "\n\nCol:";
            foreach ($col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . implode(', ', $s) . ')';
            }
        }

        $foreign_col = $db->getForeignCol();
        $this->assertNotError($foreign_col);
        if ($this->verbose > 0) {
            print "\n\nForeignCol:";
            foreach ($foreign_col as $column => $tables) {
                $s = array();
                foreach ($tables as $table) {
                    $s[] = $table;
                }
                print "\n$column : (" . 
                      implode(', ', $s) . ')';
            }
        }

        $ref = $db->getRef();
        $this->assertNotError($ref);
        if ($this->verbose > 0) {
            print "\n\nRef:";
            foreach ($ref as $ftable => $references) {
                foreach ($references as $rtable => $ref) {
                    print "\n$ftable => $rtable";
                }
            }
        }
            
        $ref_to = $db->getRefTo();
        $this->assertNotError($ref_to);
        if ($this->verbose > 0) {
            print "\n\nRefTo:";
            foreach ($ref_to as $rtable => $references) {
                $s = array();
                foreach ($references as $ftable) {
                    $s[] = $ftable;
                }
                print "\n$rtable <= (" . 
                      implode(', ', $s) . ')';
            }
        }

        $link = $db->getLink();
        $this->assertNotError($link);
        if ($this->verbose > 0) {
            print "\n\nLink:";
            foreach ($link as $table1 => $list) {
                foreach ($list as $table2 => $links) {
                    $s = array();
                    foreach ($links as $link_table) {
                        $s[] = $link_table;
                    }
                    print "\n$table1, $table2 : (" . 
                          implode(', ', $s) . ')';
                }
            }
        }
    }

}

?>
