using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
//using Oracle.DataAccess.Client;
//using Oracle.DataAccess.Types;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.oracle.adr
{
    public class AdrClaimsDao : IClaimsDao
    {
        MdoOracleConnection myCxn;

        public AdrClaimsDao(AbstractConnection cxn)
        {
            myCxn = (MdoOracleConnection)cxn;
        }

        public List<Person> getClaimants(string lastName, string firstName, string middleName, string dob, Address addr, int maxrex)
        {
            BuildGetClaimantsRequestTemplate bldTemplate = new AdrBuildGetClaimantsRequest();
            string sql = bldTemplate.buildGetClaimantsRequest(lastName, firstName, middleName, dob, addr, maxrex);
            OracleClaimsDao oracleDao = new OracleClaimsDao(myCxn);
            return oracleDao.getClaimants(sql);
        }

        public ProstheticClaim[] getProstheticClaimsForClaimant()
        {
            throw new NotImplementedException();
        }

        public ProstheticClaim[] getProstheticClaimsForClaimant(string dfn)
        {
            throw new NotImplementedException();
        }

        public List<ProstheticClaim> getProstheticClaims(string dfn, List<string> episodeDates)
        {
            throw new NotImplementedException();
        }
    }

    internal class AdrBuildGetClaimantsRequest : BuildGetClaimantsRequestTemplate
    {
        StringDictionary fldNames;

        public AdrBuildGetClaimantsRequest() : base() 
        {
            fldNames = new StringDictionary();
            fldNames.Add("ID", "person_id");
            fldNames.Add("LastName", "last_name");
            fldNames.Add("FirstName", "first_name");
            fldNames.Add("MiddleName", "middle_name");
            fldNames.Add("DOB", "date_of_birth");
            fldNames.Add("Zipcode", "zip_code");
            fldNames.Add("State", "state_code");
            fldNames.Add("City", "city");
        }

        public override string Tables
        {
            get { return AdrConstants.GET_CLAIMANTS_TABLES; }
        }

        public override string Fields
        {
            get { return AdrConstants.GET_CLAIMANTS_FIELDS; }
        }

        public override string Where
        {
            get { return AdrConstants.GET_CLAIMANTS_WHERE; }
        }

        public override StringDictionary FieldNames
        {
            get { return fldNames; }
        }
    }
}
