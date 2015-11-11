using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieEncounterDao
    {
        public FhieEncounterDao(AbstractConnection cxn)
        {
        }

        public Appointment[] getAppointments() {return null;}
        public Appointment[] getFutureAppointments() { return null; }
        public string getAppointmentText(string apptId) { return null; }
        public Adt[] getInpatientMoves(String fromDate, string toDate) { return null; }
        public Adt[] getInpatientMoves() { return null; }
        public IndexedHashtable lookupLocations(string target, string direction) { return null; }
        public HospitalLocation[] getWards() { return null; }
        public InpatientStay[] getStaysForWard(string wardId) { return null; }
        public Drg[] getDRGRecords() { return null; }
    }
}
