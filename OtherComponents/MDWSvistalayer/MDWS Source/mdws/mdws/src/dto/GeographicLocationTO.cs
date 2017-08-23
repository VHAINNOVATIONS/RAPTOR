using System;
using System.Data;
using System.Configuration;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class GeographicLocationTO : AbstractTO
    {
        public string zipcode;
        public string zipcodeType;
        public string cityName;
        public string cityType;
        public string countyName;
        public string countyFips;
        public string stateName;
        public string stateAbbreviation;
        public string stateFips;
        public string msaCode;
        public string areaCode;
        public string timeZone;
        public int utc;
        public bool daylightSavings;
        public double latitude;
        public double longitude;

        public GeographicLocationTO() { }

        public GeographicLocationTO(GeographicLocation mdo)
        {
            this.zipcode = mdo.Zipcode;
            this.zipcodeType = mdo.ZipcodeType;
            this.cityName = mdo.CityName;
            this.cityType = mdo.CityType;
            this.countyName = mdo.CountyName;
            this.countyFips = mdo.CountyFips;
            this.stateName = mdo.StateName;
            this.stateAbbreviation = mdo.StateAbbreviation;
            this.stateFips = mdo.StateFips;
            this.msaCode = mdo.MsaCode;
            this.areaCode = mdo.AreaCode;
            this.timeZone = mdo.TimeZone;
            this.utc = mdo.Utc;
            this.daylightSavings = mdo.DaylightSavings;
            this.latitude = mdo.Latitude;
            this.longitude = mdo.Longitude;
        }
    }
}
