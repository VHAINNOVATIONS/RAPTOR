using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class InpatientStay
    {
        Patient patient;
        HospitalLocation location;
        string admitTimestamp;
        string dischargeTimestamp;
        DischargeDiagnoses dischargeDiagnoses;
        string type;
        Adt[] adts;
        string movementCheckinId;

        public InpatientStay() { }

        public Patient Patient
        {
            get { return patient; }
            set { patient = value; }
        }

        public HospitalLocation Location
        {
            get { return location; }
            set { location = value; }
        }

        public string AdmitTimestamp
        {
            get { return admitTimestamp; }
            set { admitTimestamp = value; }
        }

        public string DischargeTimestamp
        {
            get { return dischargeTimestamp; }
            set { dischargeTimestamp = value; }
        }

        public DischargeDiagnoses DischargeDiagnoses
        {
            get { return dischargeDiagnoses; }
            set { dischargeDiagnoses = value; }
        }

        public string Type
        {
            get { return type; }
            set { type = value; }
        }

        public Adt[] Adts
        {
            get { return adts; }
            set { adts = value; }
        }

        public string MovementCheckinId
        {
            get { return movementCheckinId; }
            set { movementCheckinId = value; }
        }
    }
}
