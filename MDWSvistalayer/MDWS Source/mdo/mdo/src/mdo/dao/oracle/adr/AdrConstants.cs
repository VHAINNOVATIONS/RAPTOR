using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.adr
{
    public class AdrConstants
    {
        public const string DEFAULT_CXN_STRING = "Data Source=" +
            "(DESCRIPTION=" +
                "(ADDRESS=" +
                    "(PROTOCOL=TCP)" +
                    "(HOST=edbdbs4.aac.va.gov)" +
                    "(PORT=1591)" +
                ")" +
                "(CONNECT_DATA=" +
                    "(SERVICE_NAME=ADRRP.aac.va.gov)" +
                ")" +
            ");" +
            "User ID=vhaanngilloj;" +
            "Password=gumshoe5000_;";

        public const string GET_CLAIMANTS_TABLES = 
            "psim.rpt_psim_traits t," +
            "adr.person p," +
            "adr.address a," +
            "adr.phone h," +
            "sdsadm.std_phonecontacttype c," +
            "adr.email e";

        public const string GET_CLAIMANTS_FIELDS =
            "p.person_id as Id," +
            "t.last_name as LastName," +
            "t.first_name as FirstName," +
            "t.middle_name as MiddleName," +
            "t.prefix as NamePrefix," +
            "t.suffix as NameSuffix," +
            "t.gender_code as Gender," +
            "t.ssn," +
            "t.date_of_birth as DOB," +
            "a.address_line1 as Street1," +
            "a.address_line2 as Street2," +
            "a.address_line3 as Street3," +
            "a.city as City," +
            "a.state_code as State," +
            "a.county_code as County," +
            "a.zip_code as Zipcode," +
            "a.zip_plus_4 as ZipSuffix," +
            "a.postal_code as PostalCode," +
            "c.name as PhoneType," +
            "h.phone_number as PhoneNumber," +
            "e.email_address as Email";

        public const string GET_CLAIMANTS_WHERE =
            "p.vpid_id=t.vpid_id and " +
            "p.person_id=a.person_id (+) and " +
            "p.person_id=h.person_id (+) and " +
            "p.person_id=e.person_id (+) and " +
            "h.phone_type_id=c.id";

    }
}
