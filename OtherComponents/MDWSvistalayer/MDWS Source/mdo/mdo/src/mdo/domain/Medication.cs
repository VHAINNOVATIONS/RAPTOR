using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.domain.ccr;

namespace gov.va.medora.mdo
{
    public class Medication
    {
        const string DAO_NAME = "IPharmacyDao";

        public class MedicationType
        {
            public static MedicationType OUTPATIENT = new MedicationType("OP");
            public static MedicationType IV = new MedicationType("IV");
            public static MedicationType NONVA = new MedicationType("NV");
            public static MedicationType UNITDOSE = new MedicationType("UD");
            public static MedicationType INFOROUT = new MedicationType("CP");

            public static IEnumerable<MedicationType> Values
            {
                get
                {
                    yield return OUTPATIENT;
                    yield return IV;
                    yield return NONVA;
                    yield return UNITDOSE;
                    yield return INFOROUT;
                }
            }

            string code;

            MedicationType(string code) { this.code = code; }

            public string Code { get { return this.code; } }
            public override string ToString() { return Code; }
            public static MedicationType valueOf(string code)
            {
                foreach (MedicationType type in Values)
                {
                    if (code.ToUpper().Equals(type.Code))
                        return type;
                }
                return null;
            }

        }

        string id;
        string name;
        string rxNum;
        string pharmId;
        string quantity;
        string expirationDate;
        string issueDate;
        string startDate;
        string stopDate;
        string orderId;
        string status;
        string refills;
        bool outpatient;
        bool inpatient;
        bool IV;
        bool unitDose;
        bool nonVA;
        bool imo;
        string lastFillDate;
        string remaining;
        SiteId facility;
        Author provider;
        string cost;
        string sig;
        string type;
        string additives;
        string solution;
        string rate;
        string route;
        string dose;
        string instruction;
        string comment;
        string dateDocumented;
        Author documentor;
        string detail;
        string schedule;
        string daysSupply;
        KeyValuePair<string, string> pharmacyOrderableItem;
        KeyValuePair<string, string> drug;
        KeyValuePair<string, string> hospital;
        bool isSupply;

        public Medication() { }

        public bool IsSupply
        {
            get { return isSupply; }
            set { isSupply = value; }
        }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string RxNumber
        {
            get { return rxNum; }
            set { rxNum = value; }
        }

        public string PharmacyId
        {
            get { return pharmId; }
            set { pharmId = value; }
        }

        public string Quantity
        {
            get { return quantity; }
            set { quantity = value; }
        }

        public string ExpirationDate
        {
            get { return expirationDate; }
            set { expirationDate = value; }
        }

        public string IssueDate
        {
            get { return issueDate; }
            set { issueDate = value; }
        }

        public string OrderId
        {
            get { return orderId; }
            set { orderId = value; }
        }

        public string Status
        {
            get { return status; }
            set { status = value; }
        }

        public string Refills
        {
            get { return refills; }
            set { refills = value; }
        }

        public bool IsOutpatient
        {
            get { return outpatient; }
            set { outpatient = value; }
        }

        public bool IsInpatient
        {
            get { return inpatient; }
            set { inpatient = value; }
        }

        public bool IsIV
        {
            get { return IV; }
            set { IV = value; }
        }

        public bool IsUnitDose
        {
            get { return unitDose; }
            set { unitDose = value; }
        }

        public bool IsNonVA
        {
            get { return nonVA; }
            set { nonVA = value; }
        }

        public bool IsImo
        {
            get { return imo; }
            set { imo = value; }
        }

        public string LastFillDate
        {
            get { return lastFillDate; }
            set { lastFillDate = value; }
        }

        public string Remaining
        {
            get { return remaining; }
            set { remaining = value; }
        }

        public SiteId Facility
        {
            get { return facility; }
            set { facility = value; }
        }

        public Author Provider
        {
            get { return provider; }
            set { provider = value; }
        }

        public string Cost
        {
            get { return cost; }
            set { cost = value; }
        }

        public string Sig
        {
            get { return sig; }
            set { sig = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public string Additives
        {
            get { return additives; }
            set { additives = value; }
        }

        public string Solution
        {
            get { return solution; }
            set { solution = value; }
        }

        public string Rate
        {
            get { return rate; }
            set { rate = value; }
        }

        public string Route
        {
            get { return route; }
            set { route = value; }
        }

        public string Dose
        {
            get { return dose; }
            set { dose = value; }
        }

        public string Instruction
        {
            get { return instruction; }
            set { instruction = value; }
        }

        public string Comment
        {
            get { return comment; }
            set { comment = value; }
        }

        public string StartDate
        {
            get { return startDate; }
            set { startDate = value; }
        }

        public string StopDate
        {
            get { return stopDate; }
            set { stopDate = value; }
        }

        public string DateDocumented
        {
            get { return dateDocumented; }
            set { dateDocumented = value; }
        }

        public Author Documentor
        {
            get { return documentor; }
            set { documentor = value; }
        }

        public string Detail
        {
            get { return detail; }
            set { detail = value; }
        }

        public string Schedule
        {
            get { return schedule; }
            set { schedule = value; }
        }

        public string DaysSupply
        {
            get { return daysSupply; }
            set { daysSupply = value; }
        }

        public KeyValuePair<string, string> PharmacyOrderableItem
        {
            get { return pharmacyOrderableItem; }
            set { pharmacyOrderableItem = value; }
        }

        public KeyValuePair<string, string> Drug
        {
            get { return drug; }
            set { drug = value; }
        }

        public KeyValuePair<string, string> Hospital
        {
            get { return hospital; }
            set { hospital = value; }
        }

        /// <summary>
        /// Create a string representation of the Medication object using a few of the unique properties
        /// </summary>
        /// <returns>String representation of Medication object</returns>
        public override string ToString()
        {
            return "Medication" + Environment.NewLine +
                "----------" + Environment.NewLine + 
                "ID: " + id + Environment.NewLine + 
                "Name: " + name + Environment.NewLine + 
                "RxNum: " + rxNum + Environment.NewLine + 
                "Pharmacy ID: " + pharmId + Environment.NewLine + 
                "Order ID: " + orderId + Environment.NewLine + 
                "Detail: " + detail + Environment.NewLine;
        }

        internal static IPharmacyDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getPharmacyDao(cxn);
        }

        public static IndexedHashtable getOutpatientMeds(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getOutpatientMeds", new object[] { });
        }

        public static IndexedHashtable getIvMeds(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getIvMeds", new object[] { });
        }

        public static IndexedHashtable getUnitDoseMeds(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getUnitDoseMeds", new object[] { });
        }

        public static IndexedHashtable getOtherMeds(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getOtherMeds", new object[] { });
        }

        public static IndexedHashtable getAllMeds(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getAllMeds", new object[] { });
        }

        public static string getMedicationDetail(AbstractConnection cxn, string medId)
        {
            return getDao(cxn).getMedicationDetail(medId);
        }

        public static IndexedHashtable getOutpatientRxProfile(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getOutpatientRxProfile", new object[] { });
        }

        public static IndexedHashtable getMedsAdminHx(ConnectionSet cxns, string fromDate, string toDate, int nrpts)
        {
            return cxns.query(DAO_NAME, "getMedsAdminHx", new object[] { fromDate, toDate, nrpts });
        }

        public static IndexedHashtable getMedsAdminLog(ConnectionSet cxns, string fromDate, string toDate, int nrpts)
        {
            return cxns.query(DAO_NAME, "getMedsAdminLog", new object[] { fromDate, toDate, nrpts });
        }

        public static IndexedHashtable getImmunizations(ConnectionSet cxns, string fromDate, string toDate, int nrpts)
        {
            return cxns.query(DAO_NAME, "getImmunizations", new object[] { fromDate, toDate, nrpts });
        }

        public IndexedHashtable getDiscontinueReasons(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getDiscontinueReasons", new object[] { });
        }

        public static IndexedHashtable getImoMeds(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getInpatientForOutpatientMeds", new object[] { });
        }

        /// <summary>
        /// Convert a MDO Medication object to a CCR StructuredProductType. Since there is not a 1 to 1 mapping
        /// between the two objects' properties, the additional arguments should be supplied
        /// </summary>
        /// <param name="med"></param>
        /// <returns></returns>
        public StructuredProductType toStructuredProductType(Medication med, string units, string form, string unitsPerDose)
        {
            StructuredProductType newMed = new CCRHelper().buildMedObject(med.Name, med.Id, med.PharmacyId, med.OrderId,
                med.RxNumber, med.StartDate, med.StopDate, med.IssueDate, med.LastFillDate, med.ExpirationDate,
                med.Sig, med.Dose, units, form, unitsPerDose, med.Schedule, med.Route, med.Refills, med.Remaining, med.Quantity,
                med.Provider.Name, med.Provider.Id, med.Status, med.Type);
            return newMed;
        }
    }
}
