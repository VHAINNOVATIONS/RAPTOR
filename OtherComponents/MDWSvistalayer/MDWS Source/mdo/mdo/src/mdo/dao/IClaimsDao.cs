using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IClaimsDao
    {
        List<Person> getClaimants(string lastName, string firstName, string middleName, string dob, Address addr, int maxrex);
        ProstheticClaim[] getProstheticClaimsForClaimant();
        ProstheticClaim[] getProstheticClaimsForClaimant(string dfn);
        List<ProstheticClaim> getProstheticClaims(string dfn, List<string> episodeDates);
    }
}
