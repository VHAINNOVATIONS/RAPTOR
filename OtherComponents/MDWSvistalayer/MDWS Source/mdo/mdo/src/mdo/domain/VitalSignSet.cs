using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using gov.va.medora.mdo.dao;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdo
{
    public class VitalSignSet
    {
        const int NSIGNS = 14;

        string _entered;
        string timestamp;
        SiteId facility;
        HospitalLocation _location;
        //ArrayList vitalSigns;
        Dictionary<string,VitalSign> theSigns = new Dictionary<string,VitalSign>(NSIGNS);
        string units;
        string qualifiers;

        public VitalSignSet() { }

        public string Entered
        {
            get { return _entered; }
            set { _entered = value; }
        }

        public HospitalLocation Location
        {
            get { return _location; }
            set { _location = value; }
        }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public SiteId Facility
        {
            get { return facility; }
            set { facility = value; }
        }

        public VitalSign[] VitalSigns
        {
            get
            {
                //VitalSign[] result = new VitalSign[Count];
                //int i = 0;
                //foreach (KeyValuePair<string, VitalSign> kvp in theSigns)
                //{
                //    result[i++] = kvp.Value;
                //}
                //return result;
                VitalSign[] result2 = new VitalSign[theSigns.Count];
                theSigns.Values.CopyTo(result2, 0);
                return result2;
            }
        }

        public void addVitalSign(string type, VitalSign s)
        {
            //if (!VitalSign.IsValidType(type))
            //{
            //    throw new Exception("Invalid Vital Sign: " + type);
            //}
            if (theSigns.ContainsKey(type))
            {
                throw new Exception("Set already contains " + type);
            }
            theSigns.Add(type, s);

            if (type == VitalSign.BLOOD_PRESSURE)
            {
                string[] parts = StringUtils.split(s.Value1, StringUtils.SLASH);
                if (parts.Length == 2)
                {
                    VitalSign vs = new VitalSign();
                    vs.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.SYSTOLIC_BP);
                    vs.Timestamp = s.Timestamp;
                    vs.Value1 = parts[0];
                    vs.Units = s.Units;
                    vs.Qualifiers = s.Qualifiers;
                    theSigns.Add(VitalSign.SYSTOLIC_BP, vs);
                    vs = new VitalSign();
                    vs.Type = new ObservationType("", VitalSign.VITAL_SIGN, VitalSign.DIASTOLIC_BP);
                    vs.Timestamp = s.Timestamp;
                    vs.Value1 = parts[1];
                    vs.Units = s.Units;
                    vs.Qualifiers = s.Qualifiers;
                    theSigns.Add(VitalSign.DIASTOLIC_BP, vs);
                }
            }
        }

        public VitalSign getVitalSign(string type)
        {
            if (theSigns != null && theSigns.ContainsKey(type))
            {
                return (VitalSign)theSigns[type];
            }
            return null;
        }

        public int Count
        {
            get { return theSigns.Count; }
        }

        public string Qualifiers
        {
            get { return qualifiers; }
            set { qualifiers = value; }
        }

        public string Units
        {
            get { return units; }
            set { units = value; }
        }

        const string DAO_NAME = "IVitalsDao";

        internal static IVitalsDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getVitalsDao(cxn);
        }

        public static VitalSignSet[] getVitalSigns(AbstractConnection cxn)
        {
            return getDao(cxn).getVitalSigns();
        }

        public static VitalSignSet[] getVitalSigns(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getVitalSigns(pid);
        }

        public static VitalSignSet[] getVitalSigns(AbstractConnection cxn, string fromDate, string toDate, int maxRex)
        {
            return getDao(cxn).getVitalSigns(fromDate, toDate, maxRex);
        }

        public static VitalSignSet[] getVitalSigns(AbstractConnection cxn, string pid, string fromDate, string toDate, int maxRex)
        {
            return getDao(cxn).getVitalSigns(pid, fromDate,toDate, maxRex);
        }

        public static IndexedHashtable getVitalSigns(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getVitalSigns", new object[] { } );
        }

        public static IndexedHashtable getVitalSigns(ConnectionSet cxns, string fromDate, string toDate, int maxRex)
        {
            return cxns.query(DAO_NAME, "getVitalSigns", new object[] { fromDate, toDate, maxRex });
        }
    }
}
