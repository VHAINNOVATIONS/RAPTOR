using System;
using System.Collections.Generic;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo
{
    public class ProstheticClaim : Claim
    {
        const string DAO_NAME = "IClaimsDao";

        string itemId;
        string name;

        public string ItemId
        {
            get { return itemId; }
            set { itemId = value; }
        }

        public string ItemName
        {
            get { return name; }
            set { name = value; }
        }

        internal static IClaimsDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getClaimsDao(cxn);
        }

        public static ProstheticClaim[] getProstheticClaimsForPatient(AbstractConnection cxn)
        {
            return getDao(cxn).getProstheticClaimsForClaimant();
        }

        public static ProstheticClaim[] getProstheticClaimsForPatient(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getProstheticClaimsForClaimant(pid);
        }

        public static IndexedHashtable getProstheticClaimsForPatient(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getProstheticClaimsForPatient", new object[] { });
        }

        public static List<ProstheticClaim> getProstheticClaims(AbstractConnection cxn, string pid, List<string> episodeDates)
        {
            return getDao(cxn).getProstheticClaims(pid, episodeDates);
        }
    }
}
