using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.vadir
{
    public class VadirConstants
    {
        public const string DEFAULT_CXN_STRING = "Data Source=" +
            "(DESCRIPTION=" +
                "(ADDRESS_LIST=" +
                    "(ADDRESS=" +
                        "(PROTOCOL=TCP)" +
                        "(HOST=10.224.60.69)" +
                        "(PORT=1521)" +
                    ")" +
                ")" +
                "(CONNECT_DATA=" +
                    "(SERVICE_NAME=VISP1P.AAC.VA.GOV)" +
                ")" +
            ");" +
            "User ID=;" +
            "Password=;";

        public const string GET_CLAIMANTS_TABLES =
            "vis.pn p," +
            "va.dod_va_ma a," +
            "vis.tnum h," +
            "va.dod_va_tnum w," +
            "va.dod_va_ema e";
 
        public const string GET_CLAIMANTS_FIELDS =
           "p.va_id as Id," +
            "p.pn_lst_nm as LastName," +
            "p.pn_1st_nm as FirstName," +
            "p.pn_mid_nm as MiddleName," +
            "to_char(p.pn_brth_dt,'YYYYMMDD') as DOB," +
            "p.pn_sex_cd as Gender," +
            "p.pn_id as SSN," +
            "a.ma_ln1_tx as Street1," +
            "a.ma_ln2_tx as Street2," +
            "a.ma_city_nm as City," +
            "a.ma_st_cd as State," +
            "a.ma_pr_zip_cd as Zipcode," +
            "a.ma_pr_zipx_cd as ZipSuffix," +
            "h.tnum_typ_cd as PhoneType," +
            "h.phone_num as PhoneNumber," +
            "w.tnum_typ_cd as PhoneType2," +
            "w.tnum_cd as PhoneNumber2," +
            "e.ema_tx as Email";

        public const string GET_CLAIMANTS_WHERE =
            "p.va_id=a.va_id (+) and " +
            "p.va_id=h.va_id (+) and " +
            "p.va_id=w.va_id (+) and " +
            "p.va_id=e.va_id (+)";

    }
}
