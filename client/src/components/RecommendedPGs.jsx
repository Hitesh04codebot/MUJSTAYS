import { useEffect, useState } from "react";
import pgsData from "../api/pgs.json"

export default function RecommendedPGs() {
  const [pgs, setPgs] = useState([]);

  useEffect(() => {
    const filtered = pgsData.filter(pg => pg.rating >= 4.3 && pg.distance_to_college_km <= 1.5).sort((a, b) =>b.rating - a.rating && a.distance_to_college_km - b.distance_to_college_km).slice(0, 6);

    setPgs(filtered);
  }, []);

  return (
  <div className="p-6 mt-5 mx-6">
  <h2 className="text-3xl font-bold text-center">Top Recommended PGs</h2>
  <p className="font-extralight text-center text-gray-900 mb-6">Our top recommended PGs and hostels bring you the perfect balance<br/> of comfort, affordability, and convenience — chosen for their highest ratings<br/> and closest distance to campus.</p>
  <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
    {pgs.map(pg => (
      <div
        key={pg.id}
        className="bg-white border border-gray-500 rounded-xl shadow-md hover:shadow-xl transition overflow-hidden"
      >
        {/* Image */}
        <img
          src={pg.image}
          alt={pg.name}
          className="w-full h-40 object-cover"
        />

        {/* Content */}
        <div className="p-4">
          <h3 className="text-lg font-semibold mb-1">{pg.name}</h3>
          <p className="text-gray-600 text-sm mb-2">
            ⭐ {pg.rating} | {pg.distance_to_college_km} km from college
          </p>
          <p className="text-blue-600 font-bold">
            ₹{pg.price_per_month}/month
          </p>
        </div>
      </div>
    ))}
  </div>
</div>

  );
}
