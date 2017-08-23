using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class HospitalLocationTO : AbstractTO
    {
        public string id = "";
        public string name = "";
        public TaggedText department;
        public TaggedText service;
        public TaggedText specialty;
        public TaggedText stopCode;
        public SiteTO facility;
        public string building = "";
        public string floor = "";
        public string room = "";
        public string bed = "";
        public string status = "";
        public string phone = "";
        public string appointmentTimestamp = "";
        public string type = "";
        public string physicalLocation = ""; // free text description string - parsability is undefined. see #2917
        public bool askForCheckIn; // file 44 field 24
        public string appointmentLength; // file 44 field 1912
        public string clinicDisplayStartTime; // file 44, field 1914
        public string displayIncrements; // file 44, field 1917
        public TimeSlotArray availability;

        public HospitalLocationTO() { }

        public HospitalLocationTO(HospitalLocation mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.department = new TaggedText(mdo.Department);
            this.service = new TaggedText(mdo.Service);
            this.specialty = new TaggedText(mdo.Specialty);
            this.stopCode = new TaggedText(mdo.StopCode);
            if (mdo.Facility != null)
            {
                this.facility = new SiteTO(mdo.Facility);
            }
            this.building = mdo.Building;
            this.floor = mdo.Floor;
            this.room = mdo.Room;
            this.bed = mdo.Bed;
            this.status = mdo.Status;
            this.phone = mdo.Phone;
            this.appointmentTimestamp = mdo.AppointmentTimestamp;
            this.type = mdo.Type;
            this.physicalLocation = mdo.PhysicalLocation;
            this.askForCheckIn = mdo.AskForCheckIn;
            this.appointmentLength = mdo.AppointmentLength;
            this.clinicDisplayStartTime = mdo.ClinicDisplayStartTime;
            this.displayIncrements = mdo.DisplayIncrements;
            this.availability = new TimeSlotArray(mdo.Availability);
        }
    }
}
