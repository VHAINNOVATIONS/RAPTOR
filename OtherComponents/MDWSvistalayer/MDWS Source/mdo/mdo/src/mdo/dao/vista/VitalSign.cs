using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;

namespace gov.va.medora.mdo
{
    public class VitalSign : Observation
    {
        public static string VITAL_SIGN = "Vital Sign";
        public static string TEMPERATURE = "Temperature";
        public static string PULSE = "Pulse";
        public static string RESPIRATION = "Respiration";
        public static string BLOOD_PRESSURE = "Blood Pressure";
        public static string SYSTOLIC_BP = "Systolic Blood Pressure";
        public static string DIASTOLIC_BP = "Diastolic Blood Pressure";
        public static string HEIGHT = "Height";
        public static string WEIGHT = "Weight";
        public static string PAIN = "Pain";
        public static string PULSE_OXYMETRY = "Pulse Oxymetry";
        public static string CIRCUMFERENCE_GIRTH = "Circumference/Girth";
        public static string CENTRAL_VENOUS_PRESSURE = "Central Venous Pressure";
        public static string BODY_MASS_INDEX = "Body Mass Index";

        static string[] validTypes = new string[]
            {
                TEMPERATURE, PULSE, RESPIRATION, BLOOD_PRESSURE, SYSTOLIC_BP, DIASTOLIC_BP,
                HEIGHT, WEIGHT, PAIN, PULSE_OXYMETRY, CIRCUMFERENCE_GIRTH, CENTRAL_VENOUS_PRESSURE,
                BODY_MASS_INDEX
            };

        string value1;
        string value2;
        string units;
        string qualifiers;

        const string DAO_NAME = "IVitalsDao";

        public VitalSign() { }

        public string Value1
        {
            get { return value1; }
            set { value1 = value; }
        }

        public string Value2
        {
            get { return value2; }
            set { value2 = value; }
        }

        public string Units
        {
            get { return units; }
            set { units = value; }
        }

        public string Qualifiers
        {
            get { return qualifiers; }
            set { qualifiers = value; }
        }

        public static bool IsValidType(string type)
        {
            for (int i = 0; i < validTypes.Length; i++)
            {
                if (type == validTypes[i])
                {
                    return true;
                }
            }
            return false;
        }

        internal static IVitalsDao getDao(AbstractConnection cxn)
        {
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getVitalsDao(cxn);
        }

        public static VitalSign[] getLatestVitalSigns(AbstractConnection cxn)
        {
            return getDao(cxn).getLatestVitalSigns();
        }

        public static VitalSign[] getLatestVitalSigns(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getLatestVitalSigns(pid);
        }

        public static IndexedHashtable getLatestVitalSigns(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getLatestVitalSigns", new object[] { });
        }
    }
}
