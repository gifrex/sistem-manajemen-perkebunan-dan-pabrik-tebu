<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class usematerialhdr extends Model
{
    protected $table = 'usematerialhdr';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode','rkhno','totalluas','flagstatus','inputby','creatdat','updateby','updatedat'
    ];



// public function selectuse($companycode, $type=0){
//     if($type == 0){
//         $sql='';
//     }else{ $sql='b.*,'; }
//     return \DB::select(
//       "
//         SELECT DISTINCT e.herbisidagroupname, ".$sql." a.* FROM usematerialhdr a 
//         LEFT JOIN usemateriallst b ON a.rkhno = b.rkhno
//         LEFT JOIN rkhhdr f ON a.rkhno = f.rkhno
//         LEFT JOIN rkhlst g ON a.rkhno = g.rkhno 
//         LEFT JOIN herbisida c ON a.companycode = c.companycode AND b.itemcode = c.itemcode
//         LEFT JOIN herbisidadosage d ON a.companycode = c.companycode AND b.itemcode = c.itemcode
//         LEFT JOIN herbisidagroup e ON a.companycode = c.companycode AND b.itemcode = c.itemcode AND g.herbisidagroupid = e.herbisidagroupid
//         WHERE a.companycode='".$companycode."'
//       "
//     );
//   }

public function selectuse($companycode, $rkhno = 0, $type = 0)
{ 
    return \DB::table('usematerialhdr as a')
        ->selectRaw('DISTINCT b.nouse, c.companyinv, c.factoryinv, g.blok, g.plot, g.luasarea, f.mandorid, h.name AS mandorname, e.activitycode,e.herbisidagroupid, e.herbisidagroupname, a.*')
        ->leftJoin('usemateriallst as b', 'a.rkhno', '=', 'b.rkhno')
        ->leftJoin('rkhhdr as f', 'a.rkhno', '=', 'f.rkhno')
        ->leftJoin('rkhlst as g', 'a.rkhno', '=', 'g.rkhno')
        ->leftJoin('herbisida as c', function ($join) {
            $join->on('a.companycode', '=', 'c.companycode')
                ->on('b.itemcode', '=', 'c.itemcode');
        })
        ->leftJoin('herbisidadosage as d', function ($join) {
            $join->on('a.companycode', '=', 'd.companycode')  
                ->on('b.itemcode', '=', 'd.itemcode');        
        })
        ->leftJoin('herbisidagroup as e', function ($join) {
            $join->on('g.herbisidagroupid', '=', 'e.herbisidagroupid');
        })
        ->leftJoin('user as h', 'f.mandorid', '=', 'h.userid')
        ->when($type == 1, function ($query) use ($rkhno) {
            $query->where('a.rkhno', $rkhno);
        }, function ($query) use ($companycode) {
            $query->where('a.companycode', $companycode);
        });
}


public function selectusematerial($companycode, $rkhno = 0)
{
    return \DB::select(
    "
    SELECT a.companycode, com.name AS 'companyname', a.rkhno, a.blok, a.plot, lk.luasrkh, l.createdat, b.flagstatus, u.nouse, u.lkhno, us.name, e.activitycode, e.herbisidagroupid, e.herbisidagroupname, 
    c.itemname, d.itemcode, d.dosageperha, c.measure, (d.dosageperha * lk.luasrkh) AS qty_siapkan, u.qty, u.qtyretur, u.noretur, u.qtydigunakan, u.costcenter, c.companyinv, c.factoryinv
    FROM rkhlst AS a
    JOIN usematerialhdr AS b ON b.rkhno = a.rkhno AND b.companycode = a.companycode
    JOIN herbisidagroup AS e ON e.herbisidagroupid = a.herbisidagroupid
    JOIN herbisidadosage AS d ON d.companycode = a.companycode AND d.herbisidagroupid = a.herbisidagroupid
    JOIN usemateriallst AS u ON u.rkhno = b.rkhno AND u.itemcode = d.itemcode AND u.companycode = b.companycode
    JOIN herbisida AS c ON c.companycode = a.companycode AND c.itemcode = d.itemcode
    JOIN lkhhdr AS l ON u.lkhno = l.lkhno AND u.companycode = l.companycode
    JOIN user AS us ON us.userid =  l.mandorid
    JOIN lkhdetailplot AS lk ON lk.lkhno = u.lkhno AND lk.blok = a.blok AND lk.plot = a.plot
    JOIN company AS com ON com.companycode = a.companycode
    WHERE a.rkhno = ? AND a.companycode = ? AND a.usingmaterial = 1
    ORDER BY a.blok, a.plot, d.itemcode;
    ",
    [$rkhno,$companycode]
    );
}





    


}