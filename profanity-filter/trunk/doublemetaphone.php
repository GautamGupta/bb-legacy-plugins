<?php

/*=================================================================*\
  # Name:		double_metaphone( $string )
  # Purpose:	Get the primary and secondary double metaphone tokens
  # Return:		Array: if secondary == primary, secondary = NULL
\*=================================================================*/

   /*
   VERSION

   DoubleMetaphone Functional 1.01

   DESCRIPTION
 
   This function implements a "sounds like" algorithm developed
   by Lawrence Philips which he published in the June, 2000 issue
   of C/C++ Users Journal.  Double Metaphone is an improved
   version of Philips' original Metaphone algorithm.
 
   COPYRIGHT
 
   Slightly adapted from the class by Stephen Woodbridge.
   Copyright 2001, Stephen Woodbridge <woodbri@swoodbridge.com>
   All rights reserved.

   http://swoodbridge.com/DoubleMetaPhone/

   This PHP translation is based heavily on the C implementation
   by Maurice Aubrey <maurice@hevanet.com>, which in turn  
   is based heavily on the C++ implementation by
   Lawrence Philips and incorporates several bug fixes courtesy
   of Kevin Atkinson <kevina@users.sourceforge.net>.
 
   This module is free software; you may redistribute it and/or
   modify it under the same terms as Perl itself.

  
   CONTRIBUTIONS
  
   17-May-2002 Geoff Caplan  http://www.advantae.com
       Bug fix: added code to return class object which I forgot to do
       Created a functional callable version instead of the class version
       which is faster if you are calling this a lot.
  

   */

function double_metaphone_2( $string )
{
   $primary   = "";
   $secondary = "";
   $positions = array();
   $current   =  0;
  	
    $current  = 0;
    $length   = strlen($string);
    $last     = $length - 1;
    $original = $string . "     ";

    $original = strtoupper($original);

    // skip this at beginning of word
    
    if (doublemetaphone_string_at($original, 0, 2, 'GN,KN,PN,WR,PS'))
      $current++;

    // Initial 'X' is pronounced 'Z' e.g. 'Xavier'
    
    if (substr($original, 0, 1) == 'X') {
      $primary   .= "S";   // 'Z' maps to 'S'
      $secondary .= "S";
	  $positions[] = $current;
      $current++;
    }

    // main loop

    while ($current < $length) {
      switch (substr($original, $current, 1)) {
        case 'A':
        case 'E':
        case 'I':
        case 'O':
        case 'U':
        case 'Y':
			/*$primary   .= 'A';
			$secondary .= 'A';
			$positions[] = $current;*/
			while ( doublemetaphone_is_vowel( $original, $current ) ) {
				$current++;
			}
			break;

        case 'B':
          // '-mb', e.g. "dumb", already skipped over ...
          $primary   .= 'P';
          $secondary .= 'P';
			$positions[] = $current;

          if (substr($original, $current + 1, 1) == 'B')
            $current += 2;
          else
            $current += 1;
          break;

        case '�':
          $primary   .= 'S';
          $secondary .= 'S';
	  $positions[] = $current;
          $current += 1;
          break;

        case 'C':
          // various gremanic
          if (($current > 1) 
              && !doublemetaphone_is_vowel($original, $current - 2)
              && doublemetaphone_string_at($original, $current - 1, 3, 'ACH')
              && ((substr($original, $current + 2, 1) != 'I')
                  && ((substr($original, $current + 2, 1) != 'E')
                      || doublemetaphone_string_at($original, $current - 2, 6, 
                                'BACHER,MACHER')))) {

            $primary   .= 'K';
            $secondary .= 'K';
	  $positions[] = $current;
            $current += 2;
            break;
          }

          // special case 'caesar'
          if (($current == 0) 
              && doublemetaphone_string_at($original, $current, 6, 
                         'CAESAR')) {
            $primary   .= 'S';
            $secondary .= 'S';
	  $positions[] = $current;
            $current += 2;
            break;
          }

          // italian 'chianti'
          if (doublemetaphone_string_at($original, $current, 4, 
                         'CHIA')) {
            $primary   .= 'K';
            $secondary .= 'K';
	  $positions[] = $current;
            $current += 2;
            break;
          }

          if (doublemetaphone_string_at($original, $current, 2, 
                         'CH')) {

            // find 'michael'
            if (($current > 0)
                && doublemetaphone_string_at($original, $current, 4, 
                         'CHAE')) {
              $primary   .= 'K';
              $secondary .= 'X';
	  $positions[] = $current;
              $current += 2;
              break;
            }

            // greek roots e.g. 'chemistry', 'chorus'
            if (($current == 0)
                && (doublemetaphone_string_at($original, $current + 1, 5, 
                         'HARAC,HARIS')
                    || doublemetaphone_string_at($original, $current + 1, 3, 
                              'HOR,HYM,HIA,HEM')
                && !doublemetaphone_string_at($original, 0, 5, 'CHORE'))) {
              $primary   .= 'K';
              $secondary .= 'K';
	  $positions[] = $current;
              $current += 2;
              break;
            }

            // germanic, greek, or otherwise 'ch' for 'kh' sound
            if ((doublemetaphone_string_at($original, 0, 4, 'VAN ,VON ')
                 || doublemetaphone_string_at($original, 0, 3, 'SCH'))
                // 'architect' but not 'arch', orchestra', 'orchid'
                || doublemetaphone_string_at($original, $current - 2, 6, 
                         'ORCHES,ARCHIT,ORCHID')
                || doublemetaphone_string_at($original, $current + 2, 1, 
                         'T,S')
                || ((doublemetaphone_string_at($original, $current - 1, 1, 
                         'A,O,U,E')
                     || ($current == 0))
                    // e.g. 'wachtler', 'weschsler', but not 'tichner'
                    && doublemetaphone_string_at($original, $current + 2, 1, 
                         'L,R,N,M,B,H,F,V,W, '))) {
              $primary   .= 'K';
              $secondary .= 'K';
	  $positions[] = $current;
            } else {
              if ($current > 0) {
                if (doublemetaphone_string_at($original, 0, 2, 'MC')) {
                  // e.g. 'McHugh'
                  $primary   .= 'K';
                  $secondary .= 'K';
	  $positions[] = $current;
                } else {
                  $primary   .= 'X';
                  $secondary .= 'K';
	  $positions[] = $current;
                }
              } else {
                $primary   .= 'X';
                $secondary .= 'X';
	  $positions[] = $current;
              }
            }
            $current += 2;
            break;
          }

          // e.g. 'czerny'
          if (doublemetaphone_string_at($original, $current, 2, 'CZ')
              && !doublemetaphone_string_at($original, $current -2, 4, 
                         'WICZ')) {
            $primary   .= 'S';
            $secondary .= 'X';
	  $positions[] = $current;
            $current += 2;
            break;
          }

          // e.g. 'focaccia'
          if (doublemetaphone_string_at($original, $current + 1, 3, 
                     'CIA')) {
            $primary   .= 'X';
            $secondary .= 'X';
	  $positions[] = $current;
            $current += 3;
            break;
          }

          // double 'C', but not McClellan'
          if (doublemetaphone_string_at($original, $current, 2, 'CC')
              && !(($current == 1) 
                   && (substr($original, 0, 1) == 'M'))) {
            // 'bellocchio' but not 'bacchus'
            if (doublemetaphone_string_at($original, $current + 2, 1,
                       'I,E,H')
                && !doublemetaphone_string_at($original, $current + 2, 2,
                          'HU')) {
              // 'accident', 'accede', 'succeed'
              if ((($current == 1)
                   && (substr($original, $current - 1, 1) == 'A'))
                  || doublemetaphone_string_at($original, $current - 1, 5,
                            'UCCEE,UCCES')) {
                $primary   .= "KS";
                $secondary .= "KS";
	  $positions[] = $current;
	  $positions[] = $current;
                // 'bacci', 'bertucci', other italian
              } else {
                $primary   .= "X";
                $secondary .= "X";
	  $positions[] = $current;
              }
              $current += 3;
              break;
            } else {
              // Pierce's rule
              $primary   .= "K";
              $secondary .= "K";
	  $positions[] = $current;
              $current += 2;
              break;
            }
          }

          if (doublemetaphone_string_at($original, $current, 2,
                     'CK,CG,CQ')) {
            $primary   .= "K";
            $secondary .= "K";
	  $positions[] = $current;
            $current += 2;
            break;
          }

          if (doublemetaphone_string_at($original, $current, 2,
                      'CI,CE,CY')) {
            // italian vs. english
            if (doublemetaphone_string_at($original, $current, 3,
                       'CIO,CIE,CIA')) {
              $primary   .= "S";
              $secondary .= "X";
	  $positions[] = $current;
            } else {
              $primary   .= "S";
              $secondary .= "S";
	  $positions[] = $current;
            }
            $current += 2;
            break;
          }

          // else
          $primary   .= "K";
          $secondary .= "K";
	  $positions[] = $current;

          // name sent in 'mac caffrey', 'mac gregor'
          if (doublemetaphone_string_at($original, $current + 1, 2,
                     ' C, Q, G')) {
            $current += 3;
          } else {
            if (doublemetaphone_string_at($original, $current + 1, 1,
                       'C,K,Q')
                && !doublemetaphone_string_at($original, $current + 1, 2,
                           'CE,CI')) {
              $current += 2;
            } else {
              $current += 1;
            }
          }
          break;

        case 'D':
          if (doublemetaphone_string_at($original, $current, 2,
                     'DG')) {
            if (doublemetaphone_string_at($original, $current + 2, 1,
                       'I,E,Y')) {
              // e.g. 'edge'
              $primary   .= "J";
              $secondary .= "J";
	  $positions[] = $current;
              $current += 3;
              break;
            } else {
              // e.g. 'edgar'
              $primary   .= "TK";
              $secondary .= "TK";
	  $positions[] = $current;
	  $positions[] = $current;
              $current += 2;
              break;
            }
          }

          if (doublemetaphone_string_at($original, $current, 2,
                     'DT,DD')) {
            $primary   .= "T";
            $secondary .= "T";
	  $positions[] = $current;
            $current += 2;
            break;
          }

          // else
          $primary   .= "T";
          $secondary .= "T";
	  $positions[] = $current;
          $current += 1;
          break;

        case 'F':
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'F')
            $current += 2;
          else
            $current += 1;
          $primary   .= "F";
          $secondary .= "F";
          break;

        case 'G':
          if (substr($original, $current + 1, 1) == 'H') {
            if (($current > 0) 
                && !doublemetaphone_is_vowel($original, $current - 1)) {
              $primary   .= "K";
              $secondary .= "K";
	  $positions[] = $current;
              $current += 2;
              break;
            }

            if ($current < 3) {
              // 'ghislane', 'ghiradelli'
              if ($current == 0) {
                if (substr($original, $current + 2, 1) == 'I') {
                  $primary   .= "J";
                  $secondary .= "J";
                } else {
                  $primary   .= "K";
                  $secondary .= "K";
                }
	  $positions[] = $current;
                $current += 2;
                break;
              }
            }

            // Parker's rule (with some further refinements) - e.g. 'hugh'
            if ((($current > 1)
                 && doublemetaphone_string_at($original, $current - 2, 1,
                           'B,H,D'))
                // e.g. 'bough'
                || (($current > 2)
                    &&  doublemetaphone_string_at($original, $current - 3, 1,
                               'B,H,D'))
                // e.g. 'broughton'
                || (($current > 3)
                    && doublemetaphone_string_at($original, $current - 4, 1,
                               'B,H'))) {
              $current += 2;
              break;
            } else {
              // e.g. 'laugh', 'McLaughlin', 'cough', 'gough', 'rough', 'tough'
              if (($current > 2)
                  && (substr($original, $current - 1, 1) == 'U')
                  && doublemetaphone_string_at($original, $current - 3, 1,
                            'C,G,L,R,T')) {
                $primary   .= "F";
                $secondary .= "F";
              } elseif (($current > 0)
                        && substr($original, $current - 1, 1) != 'I') {
                $primary   .= "K";
                $secondary .= "K";
              }
	  $positions[] = $current;
              $current += 2;
              break;
            }
          }

          if (substr($original, $current + 1, 1) == 'N') {
            if (($current == 1) && doublemetaphone_is_vowel($original, 0)
                && !doublemetaphone_Slavo_Germanic($original)) {
	  $positions[] = $current;
	  $positions[] = $current;
              $primary   .= "KN";
              $secondary .= "N ";
            } else {
              // not e.g. 'cagney'
              if (!doublemetaphone_string_at($original, $current + 2, 2,
                          'EY')
                  && (substr($original, $current + 1) != "Y")
                  && !doublemetaphone_Slavo_Germanic($original)) {
	  $positions[] = $current;
	  $positions[] = $current;
                 $primary   .= "N ";
                 $secondary .= "KN";
              } else {
	  $positions[] = $current;
	  $positions[] = $current;
                 $primary   .= "KN";
                 $secondary .= "KN";
              }
            }
            $current += 2;
            break;
          }

          // 'tagliaro'
          if (doublemetaphone_string_at($original, $current + 1, 2,
                     'LI')
              && !doublemetaphone_Slavo_Germanic($original)) {
	  $positions[] = $current;
	  $positions[] = $current;
            $primary   .= "KL";
            $secondary .= "L ";
            $current += 2;
            break;
          }

          // -ges-, -gep-, -gel- at beginning
          if (($current == 0)
              && ((substr($original, $current + 1, 1) == 'Y')
                  || doublemetaphone_string_at($original, $current + 1, 2,
                            'ES,EP,EB,EL,EY,IB,IL,IN,IE,EI,ER'))) {
	  $positions[] = $current;
            $primary   .= "K";
            $secondary .= "J";
            $current += 2;
            break;
          }

          // -ger-, -gy-
          if ((doublemetaphone_string_at($original, $current + 1, 2,
                      'ER')
               || (substr($original, $current + 1, 1) == 'Y'))
              && !doublemetaphone_string_at($original, 0, 6,
                         'DANGER,RANGER,MANGER')
              && !doublemetaphone_string_at($original, $current -1, 1,
                         'E,I')
              && !doublemetaphone_string_at($original, $current -1, 3,
                         'RGY,OGY')) {
	  $positions[] = $current;
            $primary   .= "K";
            $secondary .= "J";
            $current += 2;
            break;
          }

          // italian e.g. 'biaggi'
          if (doublemetaphone_string_at($original, $current + 1, 1,
                     'E,I,Y')
              || doublemetaphone_string_at($original, $current -1, 4,
                        'AGGI,OGGI')) {
            // obvious germanic
            if ((doublemetaphone_string_at($original, 0, 4, 'VAN ,VON ')
                 || doublemetaphone_string_at($original, 0, 3, 'SCH'))
                || doublemetaphone_string_at($original, $current + 1, 2,
                          'ET')) {
	  $positions[] = $current;
              $primary   .= "K";
              $secondary .= "K";
            } else {
              // always soft if french ending
              if (doublemetaphone_string_at($original, $current + 1, 4,
                         'IER ')) {
	  $positions[] = $current;
                $primary   .= "J";
                $secondary .= "J";
              } else {
	  $positions[] = $current;
                $primary   .= "J";
                $secondary .= "K";
              }
            }
            $current += 2;
            break;
          }

	  $positions[] = $current;
          if (substr($original, $current +1, 1) == 'G')
            $current += 2;
          else
            $current += 1;

          $primary   .= 'K';
          $secondary .= 'K';
          break;

        case 'H':
          // only keep if first & before vowel or btw. 2 vowels
          if ((($current == 0) || 
               doublemetaphone_is_vowel($original, $current - 1))
              && doublemetaphone_is_vowel($original, $current + 1)) {
            $primary   .= 'H';
            $secondary .= 'H';
	  $positions[] = $current;
            $current += 2;
          } else
            $current += 1;
          break;

        case 'J':
          // obvious spanish, 'jose', 'san jacinto'
          if (doublemetaphone_string_at($original, $current, 4,
                     'JOSE')
              || doublemetaphone_string_at($original, 0, 4, 'SAN ')) {
            if ((($current == 0)
                 && (substr($original, $current + 4, 1) == ' '))
                || doublemetaphone_string_at($original, 0, 4, 'SAN ')) {
              $primary   .= 'H';
              $secondary .= 'H';
            } else {
              $primary   .= "J";
              $secondary .= 'H';
            }
	  $positions[] = $current;
            $current += 1;
            break;
          }

          if (($current == 0)
              && !doublemetaphone_string_at($original, $current, 4,
                     'JOSE')) {
            $primary   .= 'J';  // Yankelovich/Jankelowicz
            $secondary .= 'A';
	  $positions[] = $current;
          } else {
            // spanish pron. of .e.g. 'bajador'
            if (doublemetaphone_is_vowel($original, $current - 1)
                && !doublemetaphone_Slavo_Germanic($original)
                && ((substr($original, $current + 1, 1) == 'A')
                    || (substr($original, $current + 1, 1) == 'O'))) {
              $primary   .= "J";
              $secondary .= "H";
	  $positions[] = $current;
            } else {
              if ($current == $last) {
                $primary   .= "J";
                $secondary .= " ";
	  $positions[] = $current;
              } else {
                if (!doublemetaphone_string_at($original, $current + 1, 1,
                            'L,T,K,S,N,M,B,Z')
                    && !doublemetaphone_string_at($original, $current - 1, 1,
                               'S,K,L')) {
                  $primary   .= "J";
                  $secondary .= "J";
	  $positions[] = $current;
                }
              }
            }
          }

          if (substr($original, $current + 1, 1) == 'J') // it could happen
            $current += 2;
          else 
            $current += 1;
          break;

        case 'K':
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'K')
            $current += 2;
          else
            $current += 1;
          $primary   .= "K";
          $secondary .= "K";
          break;

        case 'L':
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'L') {
            // spanish e.g. 'cabrillo', 'gallegos'
            if ((($current == ($length - 3))
                 && doublemetaphone_string_at($original, $current - 1, 4,
                           'ILLO,ILLA,ALLE'))
                || ((doublemetaphone_string_at($original, $last-1, 2,
                            'AS,OS')
                  || doublemetaphone_string_at($original, $last, 1,
                            'A,O'))
                 && doublemetaphone_string_at($original, $current - 1, 4,
                           'ALLE'))) {
              $primary   .= "L";
              $secondary .= " ";
              $current += 2;
              break;
            }
            $current += 2;
          } else 
            $current += 1;
          $primary   .= "L";
          $secondary .= "L";
          break;

        case 'M':
	  $positions[] = $current;
          if ((doublemetaphone_string_at($original, $current - 1, 3,
                     'UMB')
               && ((($current + 1) == $last)
                   || doublemetaphone_string_at($original, $current + 2, 2,
                            'ER')))
              // 'dumb', 'thumb'
              || (substr($original, $current + 1, 1) == 'M')) {
              $current += 2;
          } else {
              $current += 1;
          }
          $primary   .= "M";
          $secondary .= "M";
          break;

        case 'N':
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'N') 
            $current += 2;
          else
            $current += 1;
          $primary   .= "N";
          $secondary .= "N";
          break;

        case '�':
	  $positions[] = $current;
          $current += 1;
          $primary   .= "N";
          $secondary .= "N";
          break;

        case 'P':
          if (substr($original, $current + 1, 1) == 'H') {
	  $positions[] = $current;
            $current += 2;
            $primary   .= "F";
            $secondary .= "F";
            break;
          }

	  $positions[] = $current;
          // also account for "campbell" and "raspberry"
          if (doublemetaphone_string_at($original, $current + 1, 1,
                     'P,B'))
            $current += 2;
          else
            $current += 1;
          $primary   .= "P";
          $secondary .= "P";
          break;

        case 'Q':
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'Q') 
            $current += 2;
          else 
            $current += 1;
          $primary   .= "K";
          $secondary .= "K";
          break;

        case 'R':
          // french e.g. 'rogier', but exclude 'hochmeier'
          if (($current == $last)
              && !doublemetaphone_Slavo_Germanic($original)
              && doublemetaphone_string_at($original, $current - 2, 2,
                        'IE')
              && !doublemetaphone_string_at($original, $current - 4, 2,
                         'ME,MA')) {
            $primary   .= " ";
            $secondary .= "R";
          } else {
            $primary   .= "R";
            $secondary .= "R";
          }
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'R') 
            $current += 2;
          else
            $current += 1;
          break;

        case 'S':
          // special cases 'island', 'isle', 'carlisle', 'carlysle'
          if (doublemetaphone_string_at($original, $current - 1, 3,
                     'ISL,YSL')) {
            $current += 1;
            break;
          }

          // special case 'sugar-'
          if (($current == 0)
              && doublemetaphone_string_at($original, $current, 5,
                        'SUGAR')) {
            $primary   .= "X";
            $secondary .= "S";
	  $positions[] = $current;
            $current += 1;
            break;
          }

          if (doublemetaphone_string_at($original, $current, 2,
                     'SH')) {
            // germanic
            if (doublemetaphone_string_at($original, $current + 1, 4,
                       'HEIM,HOEK,HOLM,HOLZ')) {
              $primary   .= "S";
              $secondary .= "S";
            } else {
              $primary   .= "X";
              $secondary .= "X";
            }
	  $positions[] = $current;
            $current += 2;
            break;
          }

          // italian & armenian 
          if (doublemetaphone_string_at($original, $current, 3,
                     'SIO,SIA')
              || doublemetaphone_string_at($original, $current, 4,
                        'SIAN')) {
            if (!doublemetaphone_Slavo_Germanic($original)) {
              $primary   .= "S";
              $secondary .= "X";
            } else {
              $primary   .= "S";
              $secondary .= "S";
            }
	  $positions[] = $current;
            $current += 3;
            break;
          }

          // german & anglicisations, e.g. 'smith' match 'schmidt', 'snider' match 'schneider'
          // also, -sz- in slavic language altho in hungarian it is pronounced 's'
          if ((($current == 0)
               && doublemetaphone_string_at($original, $current + 1, 1,
                         'M,N,L,W'))
              || doublemetaphone_string_at($original, $current + 1, 1,
                        'Z')) {
            $primary   .= "S";
            $secondary .= "X";
	  $positions[] = $current;
            if (doublemetaphone_string_at($original, $current + 1, 1,
                        'Z'))
              $current += 2;
            else
              $current += 1;
            break;
          }

          if (doublemetaphone_string_at($original, $current, 2,
                     'SC')) {
            // Schlesinger's rule 
            if (substr($original, $current + 2, 1) == 'H')
              // dutch origin, e.g. 'school', 'schooner'
              if (doublemetaphone_string_at($original, $current + 3, 2,
                         'OO,ER,EN,UY,ED,EM')) {
                // 'schermerhorn', 'schenker' 
                if (doublemetaphone_string_at($original, $current + 3, 2,
                           'ER,EN')) {
                  $primary   .= "X ";
                  $secondary .= "SK";
                } else {
                  $primary   .= "SK";
                  $secondary .= "SK";
	  $positions[] = $current;
	  $positions[] = $current;
                }
                $current += 3;
                break;
              } else {
                if (($current == 0) 
                    && !doublemetaphone_is_vowel($original, 3)
                    && (substr($original, $current + 3, 1) != 'W')) {
                  $primary   .= "X";
                  $secondary .= "S";
                } else {
                  $primary   .= "X";
                  $secondary .= "X";
                }
	  $positions[] = $current;
                $current += 3;
                break;
              }

              if (doublemetaphone_string_at($original, $current + 2, 1,
                         'I,E,Y')) {
                $primary   .= "S";
                $secondary .= "S";
	  $positions[] = $current;
                $current += 3;
                break;
              }

            // else
            $primary   .= "SK";
            $secondary .= "SK";
	  $positions[] = $current;
	  $positions[] = $current;
            $current += 3;
            break;
          }

          // french e.g. 'resnais', 'artois'
          if (($current == $last)
              && doublemetaphone_string_at($original, $current - 2, 2,
                        'AI,OI')) {
            $primary   .= " ";
            $secondary .= "S";
          } else {
            $primary   .= "S";
            $secondary .= "S";
          }
	  $positions[] = $current;

          if (doublemetaphone_string_at($original, $current + 1, 1,
                     'S,Z'))
            $current += 2;
          else 
            $current += 1;
          break;

        case 'T':
          if (doublemetaphone_string_at($original, $current, 4,
                     'TION')) {
            $primary   .= "X";
            $secondary .= "X";
	  $positions[] = $current;
            $current += 3;
            break;
          }

          if (doublemetaphone_string_at($original, $current, 3,
                     'TIA,TCH')) {
            $primary   .= "X";
            $secondary .= "X";
	  $positions[] = $current;
            $current += 3;
            break;
          }

          if (doublemetaphone_string_at($original, $current, 2,
                     'TH')
              || doublemetaphone_string_at($original, $current, 3,
                            'TTH')) {
            // special case 'thomas', 'thames' or germanic
            if (doublemetaphone_string_at($original, $current + 2, 2,
                       'OM,AM')
                || doublemetaphone_string_at($original, 0, 4, 'VAN ,VON ')
                || doublemetaphone_string_at($original, 0, 3, 'SCH')) {
              $primary   .= "T";
              $secondary .= "T";
            } else {
              $primary   .= "0";
              $secondary .= "T";
            }
	  $positions[] = $current;
            $current += 2;
            break;
          }

	  $positions[] = $current;
          if (doublemetaphone_string_at($original, $current + 1, 1,
                     'T,D'))
            $current += 2;
          else
            $current += 1;
          $primary   .= "T";
          $secondary .= "T";
          break;

        case 'V':
	  $positions[] = $current;
          if (substr($original, $current + 1, 1) == 'V')
            $current += 2;
          else
            $current += 1;
          $primary   .= "F";
          $secondary .= "F";
          break;

        case 'W':
          // can also be in middle of word
          if (doublemetaphone_string_at($original, $current, 2, 'WR')) {
            $primary   .= "R";
            $secondary .= "R";
	  $positions[] = $current;
            $current += 2;
            break;
          }

          if (($current == 0)
              && (doublemetaphone_is_vowel($original, $current + 1)
                  || doublemetaphone_string_at($original, $current, 2, 
                            'WH'))) {
            // Wasserman should match Vasserman 
            if (doublemetaphone_is_vowel($original, $current + 1)) {
              $primary   .= "A";
              $secondary .= "F";
            } else {
              // need Uomo to match Womo 
              $primary   .= "A";
              $secondary .= "A";
            }
	  $positions[] = $current;
          }

          // Arnow should match Arnoff
          if ((($current == $last) 
                && doublemetaphone_is_vowel($original, $current - 1))
              || doublemetaphone_string_at($original, $current - 1, 5,
                        'EWSKI,EWSKY,OWSKI,OWSKY')
              || doublemetaphone_string_at($original, 0, 3, 'SCH')) {
            $primary   .= " ";
            $secondary .= "F";
	  $positions[] = $current;
            $current += 1;
            break;
          }

          // polish e.g. 'filipowicz'
          if (doublemetaphone_string_at($original, $current, 4,
                     'WICZ,WITZ')) {
            $primary   .= "TS";
            $secondary .= "FX";
	  $positions[] = $current;
	  $positions[] = $current;
            $current += 4;
            break;
          }

          // else skip it
          $current += 1;
          break;

        case 'X':
          // french e.g. breaux 
          if (!(($current == $last)
                && (doublemetaphone_string_at($original, $current - 3, 3,
                           'IAU,EAU')
                 || doublemetaphone_string_at($original, $current - 2, 2,
                           'AU,OU')))) {
            $primary   .= "KS";
            $secondary .= "KS";
	  $positions[] = $current;
	  $positions[] = $current;
          }

          if (doublemetaphone_string_at($original, $current + 1, 1,
                     'C,X'))
            $current += 2;
          else
            $current += 1;
          break;

        case 'Z':
          // chinese pinyin e.g. 'zhao' 
          if (substr($original, $current + 1, 1) == "H") {
            $primary   .= "J";
            $secondary .= "J";
            $current += 2;
            break;
          } elseif (doublemetaphone_string_at($original, $current + 1, 2,
                           'ZO,ZI,ZA')
                    || (doublemetaphone_Slavo_Germanic($original)
                        && (($current > 0)
                            && substr($original, $current - 1, 1) != 'T'))) {
            $primary   .= "S ";
            $secondary .= "TS";
	  $positions[] = $current;
	  $positions[] = $current;
          } else {
            $primary   .= "S";
            $secondary .= "S";
	  $positions[] = $current;
          }

          if (substr($original, $current + 1, 1) == 'Z')
            $current += 2;
          else
            $current += 1;
          break;

        default:
          $current += 1;

      } // end switch

  //   printf("<br>ORIGINAL:    '%s'\n", $original);
  //   printf("<br>current:     '%s'\n", $current);
  //   printf("<br>last:        '%s'\n", end($positions));
  //   printf("<br>  PRIMARY:   '%s'\n", $primary);
  //   printf("<br>  SECONDARY: '%s'\n", $secondary);

    } // end while

    /*$primary   = substr($primary,   0, 4);
    $secondary = substr($secondary, 0, 4);
    
    if( $primary == $secondary )
    {
    	$secondary = NULL ;	
    }*/
    
    $result["primary"] = $primary ;
    $result["secondary"] = $secondary ;
	$result['positions'] = $positions;

    return $result ;

  } // end of function MetaPhone
  
  
/*=================================================================*\
  # Name:		string_at($string, $start, $length, $list)
  # Purpose:	Helper function for double_metaphone( )
  # Return:		Bool
\*=================================================================*/
  
function doublemetaphone_string_at($string, $start, $length, $list) 
{
    /*if (($start <0) || ($start >= strlen($string)))
      return 0;

    for ($i=0; $i<count($list); $i++) {
      if ($list[$i] == substr($string, $start, $length))
        return 1;
    }
    return 0;*/
	if ( strpos( $list, ',' ) === false )
		return substr( $string, $start, $length ) == $list;

	return strpos( ',' . $list . ',', ',' . substr( $string, $start, $length ) . ',' ) !== false;
  }


/*=================================================================*\
  # Name:		is_vowel($string, $pos)
  # Purpose:	Helper function for double_metaphone( )
  # Return:		Bool
\*=================================================================*/

function doublemetaphone_is_vowel($string, $pos) {
//    return ereg("[AEIOUY]", substr($string, $pos, 1));
	return strpos( 'AEIOUY', substr( $string, $pos, 1 ) ) !== false;
}


/*=================================================================*\
  # Name:		Slavo_Germanic($string, $pos)
  # Purpose:	Helper function for double_metaphone( )
  # Return:		Bool
\*=================================================================*/

function doublemetaphone_Slavo_Germanic($string) {
	return preg_match( '/W|K|CZ|WITZ/S', $string );
}
