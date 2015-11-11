using System;
using System.Collections.Generic;
using System.Text;
using System.Data.SqlClient;
using System.Configuration;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdo.dao.sql.npt
{
    public class NptClaimsDao : IClaimsDao
    {
        NptConnection myCxn;

        public NptClaimsDao(AbstractConnection cxn)
        {
            myCxn = (NptConnection)cxn;
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

        public List<Person> getClaimants(string lastName, string firstName, string middleName, string dob, Address addr, int maxrex)
        {
            throw new NotImplementedException();
        }
    }
}
