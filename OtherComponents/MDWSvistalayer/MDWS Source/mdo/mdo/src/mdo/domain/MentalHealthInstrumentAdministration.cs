using System;
using System.Collections.Generic;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class MentalHealthInstrumentAdministration
    {
        string id;
        KeyValuePair<string, string> patient;
        KeyValuePair<string, string> instrument;
        string dateAdministered;
        string dateSaved;
        KeyValuePair<string, string> orderedBy;
        KeyValuePair<string, string> administeredBy;
        bool isSigned;
        bool isComplete;
        string numberOfQuestionsAnswered;
        string transmitStatus;
        string transmitTime;
        KeyValuePair<string, string> hospitalLocation;
        MentalHealthInstrumentResultSet results;

        public MentalHealthInstrumentAdministration() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public KeyValuePair<string, string> Patient
        {
            get { return patient; }
            set { patient = value; }
        }

        public KeyValuePair<string, string> Instrument
        {
            get { return instrument; }
            set { instrument = value; }
        }

        public string DateAdministered
        {
            get { return dateAdministered; }
            set { dateAdministered = value; }
        }

        public string DateSaved
        {
            get { return dateSaved; }
            set { dateSaved = value; }
        }

        public KeyValuePair<string, string> OrderedBy
        {
            get { return orderedBy; }
            set { orderedBy = value; }
        }

        public KeyValuePair<string, string> AdministeredBy
        {
            get { return administeredBy; }
            set { administeredBy = value; }
        }

        public bool IsSigned
        {
            get { return isSigned; }
            set { isSigned = value; }
        }

        public bool IsComplete
        {
            get { return isComplete; }
            set { isComplete = value; }
        }

        public string NumberOfQuestionsAnswered
        {
            get { return numberOfQuestionsAnswered; }
            set { numberOfQuestionsAnswered = value; }
        }

        public string TransmissionStatus
        {
            get { return transmitStatus; }
            set { transmitStatus = value; }
        }

        public string TransmissionTime
        {
            get { return transmitTime; }
            set { transmitTime = value; }
        }

        public KeyValuePair<string, string> HospitalLocation
        {
            get { return hospitalLocation; }
            set { hospitalLocation = value; }
        }

        public MentalHealthInstrumentResultSet ResultSet
        {
            get { return results; }
            set { results = value; }
        }

        const string DAO_NAME = "IClinicalDao";

        internal static IClinicalDao getDao(AbstractConnection cxn)
        {
            if (!cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Unable to instantiate DAO: unconnected");
            }
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getClinicalDao(cxn);
        }

        public static List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(AbstractConnection cxn)
        {
            return getDao(cxn).getMentalHealthInstrumentsForPatient();
        }

        public static List<MentalHealthInstrumentAdministration> getMentalHealthInstrumentsForPatient(AbstractConnection cxn, string pid)
        {
            return getDao(cxn).getMentalHealthInstrumentsForPatient(pid);
        }

        public static void addMentalHealthInstrumentResultSet(AbstractConnection cxn, MentalHealthInstrumentAdministration administration)
        {
            getDao(cxn).addMentalHealthInstrumentResultSet(administration);
        }

        public static IndexedHashtable getMentalHealthInstrumentsForPatient(ConnectionSet cxns)
        {
            return cxns.query(DAO_NAME, "getMentalHealthInstrumentsForPatient", new object[] { });
        }

        public static MentalHealthInstrumentResultSet getMentalHealthInstrumentResultSet(AbstractConnection cxn, string administrationId)
        {
            return getDao(cxn).getMentalHealthInstrumentResultSet(administrationId);
        }

        public static IndexedHashtable getMentalHealthInstrumentsForPatientBySurvey(ConnectionSet cxns, string surveyName)
        {
            return cxns.query(DAO_NAME, "getMentalHealthInstrumentsForPatientBySurvey", new object[] { surveyName });
        }
	
        public static List<MentalHealthInstrumentResultSet> getMentalHealthInstrumentResultSetsBySurvey(AbstractConnection cxn, string surveyName)
        {
            return getDao(cxn).getMentalHealthInstrumentResultSetsBySurvey(surveyName);
        }

    }
}
