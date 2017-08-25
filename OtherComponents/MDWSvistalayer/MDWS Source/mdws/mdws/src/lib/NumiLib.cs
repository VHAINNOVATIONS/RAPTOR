using System;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class NumiLib
    {
        MySession mySession;

        public NumiLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedHospitalLocationArrays getWards()
        {
            TaggedHospitalLocationArrays result = new TaggedHospitalLocationArrays();
            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                EncounterLib encounterLib = new EncounterLib(mySession);
                TaggedHospitalLocationArray wards = encounterLib.getWards();
                if (wards.fault != null)
                {
                    result.fault = wards.fault;
                    return result;
                }
                result.arrays = new TaggedHospitalLocationArray[] { wards };
                result.count = result.arrays.Length;
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }
    }
}
