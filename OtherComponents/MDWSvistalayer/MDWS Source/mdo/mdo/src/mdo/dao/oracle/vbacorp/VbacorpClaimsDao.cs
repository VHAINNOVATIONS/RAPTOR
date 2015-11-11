using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
//using Oracle.DataAccess.Client;
//using Oracle.DataAccess.Types;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.oracle.vbacorp
{
    public class VbacorpClaimsDao : IClaimsDao
    {
        MdoOracleConnection myCxn;

        public VbacorpClaimsDao(AbstractConnection cxn)
        {
            myCxn = (MdoOracleConnection)cxn;
        }

        public List<Person> getClaimants(string lastName, string firstName, string middleName, string dob, Address addr, int maxrex)
        {
            BuildGetClaimantsRequestTemplate bldTemplate = new VbacorpBuildGetClaimantsRequest();
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

    class VbacorpBuildGetClaimantsRequest : BuildGetClaimantsRequestTemplate
    {
        StringDictionary fldNames;

        public VbacorpBuildGetClaimantsRequest() : base()
        {
            fldNames = new StringDictionary();
            fldNames.Add("ID", "ptcpnt_id");
            fldNames.Add("LastName", "last_nm");
            fldNames.Add("FirstName", "first_nm");
            fldNames.Add("MiddleName", "middle_nm");
            fldNames.Add("DOB", "@brthdy_dt");
            fldNames.Add("Zipcode", "zip_prefix_nbr");
            fldNames.Add("State", "postal_cd");
            fldNames.Add("City", "city_nm");
        }

        public override string Tables
        {
            get { return VbacorpConstants.GET_CLAIMANTS_TABLES; }
        }

        public override string Fields
        {
            get { return VbacorpConstants.GET_CLAIMANTS_FIELDS; }
        }

        public override string Where
        {
            get { return VbacorpConstants.GET_CLAIMANTS_WHERE; }
        }

        public override StringDictionary FieldNames
        {
            get { return fldNames; }
        }
    }
}
