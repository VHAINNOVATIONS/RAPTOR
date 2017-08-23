using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class Order
    {
        public class OrderStatus
        {
            public static OrderStatus DISCONTINUED = new OrderStatus("1","Discontinued","dc","dc"); 
            public static OrderStatus COMPLETE = new OrderStatus("2","Complete","comp","c");
            public static OrderStatus HOLD = new OrderStatus("3","Hold","hold","h"); 
            public static OrderStatus FLAGGED = new OrderStatus("4","Flagged","flag","?");
            public static OrderStatus PENDING = new OrderStatus("5", "Pending","pend","p"); 
            public static OrderStatus ACTIVE = new OrderStatus("6","Active","actv","a"); 
            public static OrderStatus EXPIRED = new OrderStatus("7", "Expired","exp","e"); 
            public static OrderStatus SCHEDULED = new OrderStatus("8","Scheduled","schd","s"); 
            public static OrderStatus PARTIAL_RESULTS = new OrderStatus("9","Partial Results","part","pr"); 
            public static OrderStatus DELAYED = new OrderStatus("10","Delayed","dlay","dly"); 
            public static OrderStatus UNRELEASED = new OrderStatus("11","Unreleased","unr","u");
            public static OrderStatus DISCONTINUED_EDIT = new OrderStatus("12","Discontinued/Edit","dc/e","dce");
            public static OrderStatus CANCELLED = new OrderStatus("13","Cancelled","canc","x"); 
            public static OrderStatus LAPSED = new OrderStatus("14","Lapsed","laps","l");
            public static OrderStatus RENEWED = new OrderStatus("15", "Renewed","rnew","rn"); 
            public static OrderStatus NO_STATUS = new OrderStatus("99","No Status","none","'");

            public static IEnumerable<OrderStatus> Values
            {
                get
                {
                    yield return DISCONTINUED;
                    yield return COMPLETE;
                    yield return HOLD;
                    yield return FLAGGED;
                    yield return PENDING;
                    yield return ACTIVE;
                    yield return EXPIRED;
                    yield return SCHEDULED;
                    yield return PARTIAL_RESULTS;
                    yield return DELAYED;
                    yield return UNRELEASED;
                    yield return DISCONTINUED_EDIT;
                    yield return CANCELLED;
                    yield return LAPSED;
                    yield return RENEWED;
                    yield return NO_STATUS;
                }
            }

            string value;
            string name;
            string shortName;
            string code;

            OrderStatus(string value, string name, string shortName, string code) { 
                this.value = value;
                this.name = name;
                this.shortName = shortName;
                this.code = code; 
            }

            public string Code { get { return this.code; } }
            public string Value { get { return this.value; } }
            public string Name { get { return this.name; } }
            public string ShortName { get { return this.shortName; } }
            public override string ToString() { return this.name; }

            public static OrderStatus valueOf(string lookup)
            {
                foreach (OrderStatus type in Values)
                {
                    string uplook = lookup.ToUpper();
                    if (uplook.Equals(type.Value.ToUpper()) || uplook.Equals(type.Code.ToUpper()) )
                        return type;
                }
                return null;
            }

        }

        String id;
        DateTime timestamp;
        String orderingServiceName;
        String treatingSpecialty;
        DateTime startDate;
        DateTime stopDate;
        String status;
        String sigStatus;
        DateTime dateSigned;
        String verifyingNurse;
        DateTime dateVerified;
        String verifyingClerk;
        String chartReviewer;
        DateTime dateReviewed;
        User provider;
        String text;
        String detail;
        String errMsg;
        bool flag;
        OrderType type;
        string patientId;
        string whoEnteredId;
        string patientLocationId;
        HospitalLocation location;
        string urgency;
        CollectionSample collectionSample;

        public Order() { }

        public String Id
        {
            get { return id; }
            set { id = value; }
        }

        public DateTime Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public String OrderingServiceName
        {
            get { return orderingServiceName; }
            set { orderingServiceName = value; }
        }

        public String TreatingSpecialty
        {
            get { return treatingSpecialty; }
            set { treatingSpecialty = value; }
        }

        public DateTime StartDate
        {
            get { return startDate; }
            set { startDate = value; }
        }

        public DateTime StopDate
        {
            get { return stopDate; }
            set { stopDate = value; }
        }

        public String Status
        {
            get { return status; }
            set { status = value; }
        }

        public String SigStatus
        {
            get { return sigStatus; }
            set { sigStatus = value; }
        }

        public DateTime DateSigned
        {
            get { return dateSigned; }
            set { dateSigned = value; }
        }

        public String VerifyingNurse
        {
            get { return verifyingNurse; }
            set { verifyingNurse = value; }
        }

        public DateTime DateVerified
        {
            get { return dateVerified; }
            set { dateVerified = value; }
        }

        public String VerifyingClerk
        {
            get { return verifyingClerk; }
            set { verifyingClerk = value; }
        }

        public String ChartReviewer
        {
            get { return chartReviewer; }
            set { chartReviewer = value; }
        }

        public DateTime DateReviewed
        {
            get { return dateReviewed; }
            set { dateReviewed = value; }
        }

        public User Provider
        {
            get { return provider; }
            set { provider = value; }
        }

        public String Text
        {
            get { return text; }
            set { text = value; }
        }

        public String Detail
        {
            get { return detail; }
            set { detail = value; }
        }

        public String ErrMsg
        {
            get { return errMsg; }
            set { errMsg = value; }
        }

        public bool Flag
        {
            get { return flag; }
            set { flag = value; }
        }

        public OrderType Type
        {
            get { return type; }
            set { type = value; }
        }

        public string PatientId
        {
            get { return patientId; }
            set { patientId = value; }
        }

        public string WhoEnteredId
        {
            get { return whoEnteredId; }
            set { whoEnteredId = value; }
        }

        public string PatientLocationId
        {
            get { return patientLocationId; }
            set { patientLocationId = value; }
        }

        public HospitalLocation Location
        {
            get { return location; }
            set { location = value; }
        }

        public string Urgency
        {
            get { return urgency; }
            set { urgency = value; }
        }

        public CollectionSample CollectionSample
        {
            get { return collectionSample; }
            set { collectionSample = value; }
        }

        const string DAO_NAME = "IOrdersDao";

        internal static IOrdersDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getOrdersDao(cxn);
        }

        public static IndexedHashtable getOrdersForPatient(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getOrdersForPatient", new object[] { });
        }

        public static Order[] getOrdersForPatient(AbstractConnection cxn)
        {
            return getDao(cxn).getOrdersForPatient();
        }

        public static Order[] getOrdersForPatient(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getOrdersForPatient(pid);
        }

        public static OrderedDictionary getOrderableItemsByName(AbstractConnection cxn, string name)
        {
            return getDao(cxn).getOrderableItemsByName(name);
        }

        public static string getOrderStatusForPatient(AbstractConnection cxn, string pid, string orderableItemId)
        {
            return getDao(cxn).getOrderStatusForPatient(pid, orderableItemId);
        }

        public static OrderedDictionary getOrderDialogsForDisplayGroup(AbstractConnection cxn, string displayGroupId)
        {
            return getDao(cxn).getOrderDialogsForDisplayGroup(displayGroupId);
        }

        public static List<OrderDialogItem> getOrderDialogItems(AbstractConnection cxn, string dialogId)
        {
            return getDao(cxn).getOrderDialogItems(dialogId);
        }

        public static Order writeSimpleOrderByPolicy(
            AbstractConnection cxn,
            Patient patient,
            String duz,
            String esig,
            String locationIen,
            String orderIen,
            DateTime startDate)
        {
            return getDao(cxn).writeSimpleOrderByPolicy(patient, duz, esig, locationIen, orderIen, startDate);
        }

    }
}