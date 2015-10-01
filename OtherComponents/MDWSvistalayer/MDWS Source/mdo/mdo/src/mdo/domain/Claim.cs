using System;
using System.Collections.Generic;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo
{
    public class Claim
    {
        const string DAO_NAME = "IClaimsDao";

        string id;
        string patientId;
        string patientName;
        string patientSsn;
        string episodeDate;
        string timestamp;
        string lastEditTimestamp;
        string insuranceName;
        string cost;
        string billableStatus;
        string condition;
        string serviceConnectedPercent;
        string consultId;
        string comment;

        public Claim() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string PatientId
        {
            get { return patientId; }
            set { patientId = value; }
        }

        public string PatientName
        {
            get { return patientName; }
            set { patientName = value; }
        }

        public string PatientSSN
        {
            get { return patientSsn; }
            set { patientSsn = value; }
        }

        public string EpisodeDate
        {
            get { return episodeDate; }
            set { episodeDate = value; }
        }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public string LastEditTimestamp
        {
            get { return lastEditTimestamp; }
            set { lastEditTimestamp = value; }
        }

        public string InsuranceName
        {
            get { return insuranceName; }
            set { insuranceName = value; }
        }

        public string Cost
        {
            get { return cost; }
            set { cost = value; }
        }

        public string BillableStatus
        {
            get { return billableStatus; }
            set { billableStatus = value; }
        }

        public string Condition
        {
            get { return condition; }
            set { condition = value; }
        }

        public string ServiceConnectedPercent
        {
            get { return serviceConnectedPercent; }
            set { serviceConnectedPercent = value; }
        }

        public string ConsultId
        {
            get { return consultId; }
            set { consultId = value; }
        }

        public string Comment
        {
            get { return comment; }
            set { comment = value; }
        }

        internal static IClaimsDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getClaimsDao(cxn);
        }

        public static IndexedHashtable getClaimants(
            ConnectionSet cxns, 
            string lastName,
            string firstName,
            string middleName,
            string dob,
            Address addr,
            int maxrex)
        {
            return cxns.query(DAO_NAME, "getClaimants", new object[] 
                { lastName, 
                  firstName,
                  middleName,
                  dob,
                  addr,
                  maxrex
                });
        }
    }
}
