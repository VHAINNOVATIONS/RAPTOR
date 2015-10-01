using System;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class AthenaLib
    {
        MySession mySession;

        public AthenaLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedTextArray getLocations(string target, string direction)
        {
            TaggedTextArray result = new TaggedTextArray();
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
                TaggedHospitalLocationArray locations = encounterLib.getLocations(target, direction);
                if (locations.fault != null)
                {
                    result.fault = locations.fault;
                    return result;
                }
                result.results = new TaggedText[locations.locations.Length];
                for (int i = 0; i < locations.locations.Length; i++)
                {
                    result.results[i] = new TaggedText(locations.locations[i].id, locations.locations[i].name);
                }
                result.count = locations.count;
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

    }
}
