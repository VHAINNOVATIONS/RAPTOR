using System;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ClosestFacilityTO : AbstractTO
    {
        public string visn;
        public string city;
        public string state;
        public string county;
        public string zip;
        public string longitude;
        public string latitude;
        public string fips;
        public string msa;
        public string urb;
        public SiteTO nearestFacility;
        public string nearestFacilityDistance;
        public string nearestFacilityMsa;
        public string nearestFacilityUrb;
        public SiteTO nearestMedicalCenter;
        public string nearestMedicalCenterDistance;
        public string nearestMedicalCenterMsa;
        public string nearestMedicalCenterUrb;
        public SiteTO nearestFacilityInRegion;
        public string nearestFacilityInRegionDistance;
        public string nearestFacilityInRegionMsa;
        public string nearestFacilityInRegionUrb;
        public SiteTO nearestMedicalCenterInRegion;
        public string nearestMedicalCenterInRegionDistance;
        public string nearestMedicalCenterInRegionMsa;
        public string nearestMedicalCenterInRegionUrb;

        public ClosestFacilityTO() { }

        public ClosestFacilityTO(ClosestFacility mdo)
        {
            this.visn = mdo.RegionId;
            this.city = mdo.City;
            this.state = mdo.State;
            this.county = mdo.County;
            this.zip = mdo.Zipcode;
            this.longitude = mdo.Longitude;
            this.latitude = mdo.Latitude;
            this.fips = mdo.Fips;
            this.msa = mdo.Msa;
            this.urb = mdo.Urb;
            if (mdo.NearestFacility != null)
            {
                this.nearestFacility = new SiteTO(mdo.NearestFacility);
                this.nearestFacilityDistance = mdo.NearestFacilityDistance;
                this.nearestFacilityMsa = mdo.NearestFacilityMsa;
                this.nearestFacilityUrb = mdo.NearestFacilityUrb;
            }
            if (mdo.NearestMedicalCenter != null)
            {
                this.nearestMedicalCenter = new SiteTO(mdo.NearestMedicalCenter);
                this.nearestMedicalCenterDistance = mdo.NearestMedicalCenterDistance;
                this.nearestMedicalCenterMsa = mdo.NearestMedicalCenterMsa;
                this.nearestMedicalCenterUrb = mdo.NearestMedicalCenterUrb;
            }
            if (mdo.NearestFacilityInRegion != null)
            {
                this.nearestFacilityInRegion = new SiteTO(mdo.NearestFacilityInRegion);
                this.nearestFacilityInRegionDistance = mdo.NearestFacilityInRegionDistance;
                this.nearestFacilityInRegionMsa = mdo.NearestFacilityInRegionMsa;
                this.nearestFacilityInRegionUrb = mdo.NearestFacilityInRegionUrb;
            }
            if (mdo.NearestMedicalCenterInRegion != null)
            {
                this.nearestMedicalCenterInRegion = new SiteTO(mdo.NearestMedicalCenterInRegion);
                this.nearestMedicalCenterInRegionDistance = mdo.NearestMedicalCenterInRegionDistance;
                this.nearestMedicalCenterInRegionMsa = mdo.NearestMedicalCenterInRegionMsa;
                this.nearestMedicalCenterInRegionUrb = mdo.NearestMedicalCenterInRegionUrb;
            }
        }
    }
}
