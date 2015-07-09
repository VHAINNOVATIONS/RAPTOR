using System;
using System.Collections.Generic;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class MentalHealthInstrumentAdministrationTO : AbstractTO
    {
        public string id;
        public TaggedText patient;
        public TaggedText instrument;
        public string dateAdministered;
        public string dateSaved;
        public TaggedText orderedBy;
        public TaggedText administeredBy;
        public bool isSigned;
        public bool isComplete;
        public string numberOfQuestionsAnswered;
        public string transmitStatus;
        public string transmitTime;
        public TaggedText hospitalLocation;
        public MentalHealthInstrumentResultSetTO results;

        public MentalHealthInstrumentAdministrationTO() { }

        public MentalHealthInstrumentAdministrationTO(MentalHealthInstrumentAdministration mdo)
        {
            this.id = mdo.Id;
            this.patient = new TaggedText(mdo.Patient);
            this.instrument = new TaggedText(mdo.Instrument);
            this.dateAdministered = mdo.DateAdministered;
            this.dateSaved = mdo.DateSaved;
            this.orderedBy = new TaggedText(mdo.OrderedBy);
            this.administeredBy = new TaggedText(mdo.AdministeredBy);
            this.isSigned = mdo.IsSigned;
            this.isComplete = mdo.IsComplete;
            this.numberOfQuestionsAnswered = mdo.NumberOfQuestionsAnswered;
            this.transmitStatus = mdo.TransmissionStatus;
            this.transmitTime = mdo.TransmissionTime;
            this.hospitalLocation = new TaggedText(mdo.HospitalLocation);
            if (mdo.ResultSet != null)
            {
                this.results = new MentalHealthInstrumentResultSetTO(mdo.ResultSet);
            }
        }
    }
}