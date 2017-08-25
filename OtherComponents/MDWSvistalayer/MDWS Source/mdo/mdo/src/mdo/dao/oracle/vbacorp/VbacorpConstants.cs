using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.vbacorp
{
    public class VbacorpConstants
    {
        public const string DEFAULT_CXN_STRING = "Data Source=" +
            "(DESCRIPTION=" +
                "(ADDRESS_LIST=" +
                    "(ADDRESS=" +
                        "(COMMUNITY=tcp)" +
                        "(PROTOCOL=TCP)" +
                        "(Host=vbadev.vba.va.gov)" +
                        "(Port=1527)" +
                    ")" +
                    "(ADDRESS=" +
                        "(COMMUNITY=tcp)" +
                        "(PROTOCOL=TCP)" +
                        "(Host=vhadev.vba.va.gov)" +
                        "(Port=1526)" +
                    ")" +
                ")" +
                "(CONNECT_DATA=" +
                    "(SID=prodtest)" +
                ")" +
            ");" +
            "User ID=s506jxg;" +
            "Password=gumshoe5_;";

        public const string GET_CLAIMANTS_TABLES =
            "corpprod.person p," +
            "corpprod.ptcpnt_addrs a," +
            "corpprod.ptcpnt_phone h";

        public const string GET_CLAIMANTS_FIELDS =
            "p.ptcpnt_id as Id," +                       
            "p.last_nm as LastName," +
            "p.first_nm as FirstName," +
            "p.middle_nm as MiddleName," +
            "to_char(p.brthdy_dt,'YYYYMMDD') as DOB," +
            "p.gender_cd as Gender," +
            "p.ssn_nbr as SSN," +
            "a.addrs_one_txt as Street1," +
            "a.addrs_two_txt as Street2," +
            "a.addrs_three_txt as Street3," +
            "a.city_nm as City," +
            "a.county_nm as County," +
            "a.zip_prefix_nbr as Zipcode," +
            "a.zip_first_suffix_nbr as ZipSuffix1," +
            "a.zip_second_suffix_nbr as ZipSuffix2," +
            "a.postal_cd as State," +
            "a.email_addrs_txt as Email," +
            "h.phone_type_nm as PhoneType," +
            "h.phone_nbr as PhoneNumber," +
            "h.area_nbr as AreaCode," +
            "h.extnsn_nbr as Extension";

        public const string GET_CLAIMANTS_WHERE =
            "p.ptcpnt_id=a.ptcpnt_id (+) and " +
            "p.ptcpnt_id=h.ptcpnt_id (+)"; 

    }
}
